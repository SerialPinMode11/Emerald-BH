<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\RentalAgreement;
use App\Models\Transaction;
use App\Services\AuditLogger;
use App\Services\RentPaymentScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Inertia\Inertia;
use Inertia\Response;

class LandOwnerController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $user = $request->user();

        $properties = Property::query()->where('land_owner_id', $user->id);
        $agreements = RentalAgreement::query()
            ->whereHas('property', fn ($q) => $q->where('land_owner_id', $user->id));

        return Inertia::render('emerald/land-owner/Dashboard', [
            'stats' => [
                'total_properties' => (clone $properties)->count(),
                'pending_approval' => (clone $properties)->where('status', 'pending')->count(),
                'active_rentals' => (clone $agreements)->where('status', 'active')->count(),
                'rental_requests' => (clone $agreements)->where('status', 'requested')->count(),
            ],
            'recentRequests' => RentalAgreement::query()
                ->with(['property', 'customer:id,name,email'])
                ->whereHas('property', fn ($q) => $q->where('land_owner_id', $user->id))
                ->whereIn('status', ['requested', 'community_review'])
                ->latest()
                ->limit(5)
                ->get(),
            'properties' => Property::query()
                ->with('media')
                ->where('land_owner_id', $user->id)
                ->latest()
                ->limit(4)
                ->get()
                ->map(fn (Property $p) => $this->formatProperty($p)),
        ]);
    }

    public function properties(Request $request): Response
    {
        $properties = Property::query()
            ->with(['media', 'rentalAgreements.customer:id,name', 'approver:id,name'])
            ->where('land_owner_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (Property $p) => [
                ...$this->formatProperty($p),
                'description' => $p->description,
                'address' => $p->address,
                'deposit' => $p->deposit,
                'terms_of_rental' => $p->terms_of_rental,
                'rejection_reason' => $p->rejection_reason,
                'approved_at' => $p->approved_at?->format('M d, Y H:i'),
                'created_at' => $p->created_at?->format('M d, Y'),
                'approver_name' => $p->approver?->name,
                'requests_count' => $p->rentalAgreements->count(),
            ]);

        return Inertia::render('emerald/land-owner/Properties', [
            'properties' => $properties,
        ]);
    }

    public function storeProperty(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'price_per_month' => ['required', 'numeric', 'min:0'],
            'deposit' => ['nullable', 'numeric', 'min:0'],
            'terms_of_rental' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'room_length_m' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'room_width_m' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'room_height_m' => ['nullable', 'numeric', 'min:1', 'max:20'],
            'ar_model' => [
                'nullable',
                File::types(['glb', 'gltf'])->max(50 * 1024),
            ],
            'ar_model_ios' => [
                'nullable',
                File::types(['usdz'])->max(50 * 1024),
            ],
        ]);

        $property = Property::query()->create([
            ...collect($validated)->except(['ar_model', 'ar_model_ios', 'image_url'])->all(),
            'land_owner_id' => $request->user()->id,
            'status' => 'pending',
            'deposit' => $validated['deposit'] ?? 0,
            'room_length_m' => $validated['room_length_m'] ?? 5,
            'room_width_m' => $validated['room_width_m'] ?? 4,
            'room_height_m' => $validated['room_height_m'] ?? 2.5,
        ]);

        if (! empty($validated['image_url'])) {
            $property->media()->create([
                'url' => $validated['image_url'],
                'type' => 'image',
                'is_primary' => true,
            ]);
        }

        if ($request->hasFile('ar_model')) {
            $path = $request->file('ar_model')->store("properties/{$property->id}/ar", 'public');
            $property->media()->create([
                'url' => Storage::disk('public')->url($path),
                'type' => 'ar_model',
                'is_primary' => false,
            ]);
        }

        if ($request->hasFile('ar_model_ios')) {
            $path = $request->file('ar_model_ios')->store("properties/{$property->id}/ar", 'public');
            $property->media()->create([
                'url' => Storage::disk('public')->url($path),
                'type' => 'ar_model_ios',
                'is_primary' => false,
            ]);
        }

        AuditLogger::log('property_created', $property, $request->user());

        $arNote = $request->hasFile('ar_model')
            ? ' AR room model uploaded.'
            : '';

        return redirect()
            ->route('land-owner.properties', ['tab' => 'requests'])
            ->with('success', 'Property submitted for Super Admin approval.'.$arNote);
    }

    public function signAgreement(Request $request, RentalAgreement $agreement): RedirectResponse
    {
        abort_unless($agreement->property->land_owner_id === $request->user()->id, 403);

        $agreement->update(['signed_by_owner' => now()]);
        AuditLogger::log('owner_signed', $agreement, $request->user());

        return back()->with('success', 'Agreement signed successfully.');
    }

    public function showPropertyRental(
        Request $request,
        Property $property,
        RentPaymentScheduleService $scheduleService,
    ): Response {
        abort_unless($property->land_owner_id === $request->user()->id, 403);
        abort_unless($property->status === 'rented', 404);

        $agreement = $this->activeRentalAgreement($property);
        $property->load('media');

        $schedule = $scheduleService->build($agreement);

        return Inertia::render('emerald/land-owner/PropertyRental', [
            'property' => $this->formatProperty($property),
            'agreement' => [
                'id' => $agreement->id,
                'start_date' => $agreement->start_date?->format('F Y'),
                'start_date_iso' => $agreement->start_date?->toDateString(),
                'end_date' => $agreement->end_date?->format('F Y'),
                'monthly_rent' => (float) $agreement->total_rent,
                'deposit_paid' => (bool) $agreement->deposit_paid,
                'status' => $agreement->status,
            ],
            'tenant' => [
                'name' => $agreement->customer->name,
                'email' => $agreement->customer->email,
            ],
            'payment_schedule' => $schedule,
            'payment_summary' => $scheduleService->summary($schedule),
        ]);
    }

    public function recordRentPayment(
        Request $request,
        Property $property,
        Transaction $transaction,
    ): RedirectResponse {
        abort_unless($property->land_owner_id === $request->user()->id, 403);
        abort_unless($property->status === 'rented', 404);

        $agreement = $this->activeRentalAgreement($property);

        abort_unless($transaction->rental_agreement_id === $agreement->id, 403);
        abort_unless($transaction->type === 'rent', 422);

        if (! in_array($transaction->status, ['pending', 'disputed', 'failed'], true)) {
            return back()->with('error', 'This payment is already recorded.');
        }

        $transaction->update([
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        AuditLogger::log('rent_payment_recorded', $transaction, $request->user());

        return back()->with('success', 'Payment marked as received.');
    }

    private function activeRentalAgreement(Property $property): RentalAgreement
    {
        $agreement = RentalAgreement::query()
            ->with('customer:id,name,email')
            ->where('property_id', $property->id)
            ->where('status', 'active')
            ->latest('start_date')
            ->first();

        abort_unless($agreement, 404);

        return $agreement;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatProperty(Property $property): array
    {
        return [
            'id' => $property->id,
            'title' => $property->title,
            'city' => $property->city,
            'price_per_month' => $property->price_per_month,
            'status' => $property->status,
            'image_url' => $property->primaryImageUrl(),
            'room_length_m' => $property->room_length_m,
            'room_width_m' => $property->room_width_m,
            'room_height_m' => $property->room_height_m,
            'ar_model_url' => $property->arModelUrl(),
            'has_ar_model' => (bool) $property->arModelUrl(),
        ];
    }
}
