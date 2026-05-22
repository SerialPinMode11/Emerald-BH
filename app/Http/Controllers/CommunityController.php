<?php

namespace App\Http\Controllers;

use App\Models\RentalAgreement;
use App\Models\Transaction;
use App\Services\AuditLogger;
use App\Services\RentPaymentScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommunityController extends Controller
{
    public function portal(Request $request): Response
    {
        $user = $request->user();

        $agreements = RentalAgreement::query()
            ->with(['property.media', 'customer:id,name,email', 'property.landOwner:id,name,email'])
            ->where('community_id', $user->id)
            ->latest()
            ->get()
            ->map(fn (RentalAgreement $a) => $this->formatAgreement($a));

        $disputes = Transaction::query()
            ->with(['rentalAgreement.property', 'rentalAgreement.customer:id,name', 'disputer:id,name'])
            ->where('status', 'disputed')
            ->whereHas('rentalAgreement', fn ($q) => $q->where('community_id', $user->id))
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('emerald/community/Portal', [
            'stats' => [
                'assigned_agreements' => $agreements->count(),
                'active_disputes' => $disputes->count(),
                'in_review' => $agreements->where('status', 'community_review')->count(),
                'active_rentals' => $agreements->where('status', 'active')->count(),
            ],
            'agreements' => $agreements,
            'disputes' => $disputes,
        ]);
    }

    public function showAgreement(Request $request, RentalAgreement $agreement): Response
    {
        abort_unless($agreement->community_id === $request->user()->id, 403);

        $agreement->load([
            'property.media',
            'property.landOwner:id,name,email',
            'customer:id,name,email',
        ]);

        return Inertia::render('emerald/community/AgreementDetail', [
            'agreement' => $this->formatAgreement($agreement),
        ]);
    }

    public function activateAgreement(Request $request, RentalAgreement $agreement): RedirectResponse
    {
        abort_unless($agreement->community_id === $request->user()->id, 403);
        abort_unless($agreement->status === 'community_review', 422);

        $validated = $request->validate([
            'community_notes' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $agreement->update([
            'status' => 'active',
            'community_notes' => $validated['community_notes'],
            'start_date' => $agreement->start_date ?? now(),
            'end_date' => $agreement->end_date ?? now()->addYear(),
        ]);

        $agreement->property->update(['status' => 'rented']);

        app(RentPaymentScheduleService::class)->sync($agreement->fresh());

        AuditLogger::log('agreement_activated', $agreement, $request->user());

        return redirect()
            ->route('community.agreements.show', $agreement)
            ->with('success', 'Rental activated. Contact sheet is ready to print.');
    }

    public function disputeResolution(Request $request, Transaction $transaction): Response
    {
        abort_unless(
            $transaction->rentalAgreement?->community_id === $request->user()->id,
            403,
        );

        $transaction->load([
            'rentalAgreement.property.media',
            'rentalAgreement.customer:id,name,email',
            'rentalAgreement.property.landOwner:id,name,email',
            'disputer:id,name',
        ]);

        $agreement = $transaction->rentalAgreement;
        if ($agreement?->property) {
            $agreement->property->setRelation(
                'land_owner',
                $agreement->property->landOwner,
            );
        }

        return Inertia::render('emerald/community/DisputeResolution', [
            'transaction' => $transaction,
            'agreement' => $transaction->rentalAgreement,
        ]);
    }

    public function updateAgreementStatus(Request $request, RentalAgreement $agreement): RedirectResponse
    {
        abort_unless($agreement->community_id === $request->user()->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:community_review,active,terminated'],
            'community_notes' => ['nullable', 'string'],
        ]);

        $old = $agreement->only(['status', 'community_notes']);
        $agreement->update($validated);

        if ($validated['status'] === 'active') {
            $agreement->property->update(['status' => 'rented']);
        }

        AuditLogger::log('agreement_status_updated', $agreement, $request->user(), $old, $validated);

        return back()->with('success', 'Agreement status updated.');
    }

    public function resolveDispute(Request $request, Transaction $transaction): RedirectResponse
    {
        abort_unless(
            $transaction->rentalAgreement?->community_id === $request->user()->id,
            403,
        );

        $validated = $request->validate([
            'resolution' => ['required', 'string'],
            'status' => ['required', 'in:completed,failed'],
        ]);

        $transaction->update([
            'resolution' => $validated['resolution'],
            'status' => $validated['status'],
        ]);

        AuditLogger::log('dispute_resolved', $transaction, $request->user());

        return redirect()
            ->route('community.portal')
            ->with('success', 'Dispute resolution recorded.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formatAgreement(RentalAgreement $agreement): array
    {
        return [
            'id' => $agreement->id,
            'status' => $agreement->status,
            'community_notes' => $agreement->community_notes,
            'start_date' => $agreement->start_date?->format('M d, Y'),
            'end_date' => $agreement->end_date?->format('M d, Y'),
            'total_rent' => $agreement->total_rent,
            'signed_by_customer' => (bool) $agreement->signed_by_customer,
            'signed_by_owner' => (bool) $agreement->signed_by_owner,
            'customer' => $agreement->customer,
            'property' => [
                'title' => $agreement->property->title,
                'city' => $agreement->property->city,
                'address' => $agreement->property->address,
                'image_url' => $agreement->property->primaryImageUrl(),
            ],
            'land_owner' => $agreement->property->landOwner,
            'can_print_contacts' => $agreement->status === 'active'
                && $agreement->signed_by_customer !== null,
        ];
    }
}
