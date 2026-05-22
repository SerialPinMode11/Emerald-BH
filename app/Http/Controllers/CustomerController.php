<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\RentalAgreement;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function explore(Request $request): Response
    {
        $query = Property::query()
            ->with(['media', 'landOwner:id,name'])
            ->where('status', 'approved');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($city = $request->string('city')->trim()->toString()) {
            $query->where('city', $city);
        }

        $properties = $query->latest()->get()->map(fn (Property $p) => $this->formatProperty($p));

        return Inertia::render('emerald/customer/ExploreProperties', [
            'properties' => $properties,
            'featured' => $properties->take(3)->values(),
            'cities' => Property::query()->where('status', 'approved')->distinct()->orderBy('city')->pluck('city'),
            'filters' => [
                'search' => $search ?? '',
                'city' => $city ?? '',
            ],
        ]);
    }

    public function showProperty(Request $request, Property $property): Response
    {
        abort_unless($property->status === 'approved', 404);

        $property->load(['media', 'landOwner:id,name']);

        $hasActiveRequest = RentalAgreement::query()
            ->where('property_id', $property->id)
            ->where('customer_id', $request->user()->id)
            ->whereNotIn('status', ['terminated', 'completed'])
            ->exists();

        return Inertia::render('emerald/customer/PropertyDetail', [
            'property' => $this->formatProperty($property),
            'hasActiveRequest' => $hasActiveRequest,
        ]);
    }

    public function rentals(Request $request): Response
    {
        $agreements = RentalAgreement::query()
            ->with(['property.media', 'property.landOwner:id,name,email'])
            ->where('customer_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (RentalAgreement $a) => [
                'id' => $a->id,
                'status' => $a->status,
                'property' => $this->formatProperty($a->property),
                'start_date' => $a->start_date?->format('M d, Y'),
                'end_date' => $a->end_date?->format('M d, Y'),
                'signed_by_customer' => (bool) $a->signed_by_customer,
                'signed_by_owner' => (bool) $a->signed_by_owner,
                'community_notes' => $a->community_notes,
            ]);

        return Inertia::render('emerald/customer/MyRentals', [
            'agreements' => $agreements,
        ]);
    }

    public function signAgreement(Request $request, RentalAgreement $agreement): Response
    {
        abort_unless($agreement->customer_id === $request->user()->id, 403);

        $agreement->load(['property.media', 'property.landOwner:id,name,email', 'transactions']);

        return Inertia::render('emerald/customer/SignAgreement', [
            'agreement' => [
                'id' => $agreement->id,
                'status' => $agreement->status,
                'property' => $this->formatProperty($agreement->property),
                'owner' => $agreement->property->landOwner,
                'start_date' => $agreement->start_date?->format('M d, Y'),
                'end_date' => $agreement->end_date?->format('M d, Y'),
                'total_rent' => $agreement->total_rent,
                'deposit_paid' => $agreement->deposit_paid,
                'signed_by_customer' => (bool) $agreement->signed_by_customer,
                'signed_by_owner' => (bool) $agreement->signed_by_owner,
                'transactions' => $agreement->transactions,
            ],
        ]);
    }

    public function requestRent(Request $request, Property $property): RedirectResponse
    {
        abort_unless($property->status === 'approved', 422);

        $exists = RentalAgreement::query()
            ->where('property_id', $property->id)
            ->where('customer_id', $request->user()->id)
            ->whereNotIn('status', ['terminated', 'completed'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'You already have an active request for this property.');
        }

        $agreement = RentalAgreement::query()->create([
            'property_id' => $property->id,
            'customer_id' => $request->user()->id,
            'total_rent' => $property->price_per_month,
            'status' => 'requested',
        ]);

        AuditLogger::log('rental_requested', $agreement, $request->user());

        return redirect()
            ->route('customer.rentals')
            ->with('success', 'Rental request submitted. A Super Admin will assign a community mediator.');
    }

    public function sign(Request $request, RentalAgreement $agreement): RedirectResponse
    {
        abort_unless($agreement->customer_id === $request->user()->id, 403);
        abort_unless(in_array($agreement->status, ['community_review', 'active'], true), 422);

        $agreement->update(['signed_by_customer' => now()]);
        AuditLogger::log('customer_signed', $agreement, $request->user());

        return redirect()
            ->route('customer.rentals')
            ->with('success', 'Agreement signed successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formatProperty(Property $property): array
    {
        return [
            'id' => $property->id,
            'title' => $property->title,
            'description' => $property->description,
            'address' => $property->address,
            'city' => $property->city,
            'price_per_month' => $property->price_per_month,
            'deposit' => $property->deposit,
            'terms_of_rental' => $property->terms_of_rental,
            'status' => $property->status,
            'image_url' => $property->primaryImageUrl(),
            'owner_name' => $property->landOwner?->name,
            'room_length_m' => $property->room_length_m,
            'room_width_m' => $property->room_width_m,
            'room_height_m' => $property->room_height_m,
            'ar_model_url' => $property->arModelUrl(),
            'ar_model_ios_url' => $property->arModelIosUrl(),
        ];
    }
}
