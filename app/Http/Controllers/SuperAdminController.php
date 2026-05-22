<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ChangeRequest;
use App\Models\Property;
use App\Models\RentalAgreement;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminController extends Controller
{
    public function dashboard(Request $request): Response
    {
        return Inertia::render('emerald/super-admin/Dashboard', [
            'stats' => $this->stats(),
            'unassignedAgreements' => RentalAgreement::query()
                ->with(['property', 'customer:id,name,email'])
                ->whereNull('community_id')
                ->where('status', 'requested')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (RentalAgreement $a) => [
                    'id' => $a->id,
                    'property' => ['title' => $a->property->title],
                    'customer' => $a->customer,
                ]),
            'mediators' => User::query()->where('role', UserRole::Community)->where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function approvals(Request $request): Response
    {
        return Inertia::render('emerald/super-admin/Approvals', [
            'pendingProperties' => Property::query()
                ->with(['landOwner:id,name,email', 'media'])
                ->where('status', 'pending')
                ->latest()
                ->get()
                ->map(fn (Property $p) => $this->formatPendingProperty($p)),
            'reviewedProperties' => Property::query()
                ->with(['landOwner:id,name', 'media', 'approver:id,name'])
                ->whereIn('status', ['approved', 'rejected'])
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (Property $p) => [
                    ...$this->formatPendingProperty($p),
                    'rejection_reason' => $p->rejection_reason,
                    'approver_name' => $p->approver?->name,
                    'approved_at' => $p->approved_at?->format('M d, Y H:i'),
                ]),
        ]);
    }

    public function approveProperty(Request $request, Property $property): RedirectResponse
    {
        abort_unless($property->status === 'pending', 422);

        $property->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        AuditLogger::log('property_approved', $property, $request->user());

        return back()->with('success', "\"{$property->title}\" approved. It is now visible to customers.");
    }

    public function rejectProperty(Request $request, Property $property): RedirectResponse
    {
        abort_unless($property->status === 'pending', 422);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $property->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => null,
            'approved_at' => null,
        ]);

        AuditLogger::log('property_rejected', $property, $request->user(), null, $validated);

        return back()->with('success', 'Property rejected. The land owner has been notified with your explanation.');
    }

    public function assignMediator(Request $request, RentalAgreement $agreement): RedirectResponse
    {
        $validated = $request->validate([
            'community_id' => ['required', 'exists:users,id'],
        ]);

        $mediator = User::query()->findOrFail($validated['community_id']);
        abort_unless($mediator->role === UserRole::Community, 422);

        $agreement->update([
            'community_id' => $mediator->id,
            'status' => 'community_review',
        ]);

        AuditLogger::log('mediator_assigned', $agreement, $request->user());

        return back()->with('success', "Mediator {$mediator->name} assigned. Rental is under community review.");
    }

    public function storeChangeRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'request_type' => ['required', 'in:feature,bugfix,config_change,ui_update'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,critical'],
        ]);

        ChangeRequest::query()->create([
            ...$validated,
            'requested_by' => $request->user()->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Change request submitted to Dev Admin.');
    }

    /**
     * @return array<string, int>
     */
    private function stats(): array
    {
        return [
            'pending_approvals' => Property::query()->where('status', 'pending')->count(),
            'active_disputes' => Transaction::query()->where('status', 'disputed')->count(),
            'new_users' => User::query()->where('created_at', '>=', now()->subDay())->count(),
            'unassigned_agreements' => RentalAgreement::query()->whereNull('community_id')->where('status', 'requested')->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPendingProperty(Property $property): array
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
            'room_length_m' => $property->room_length_m,
            'room_width_m' => $property->room_width_m,
            'room_height_m' => $property->room_height_m,
            'created_at' => $property->created_at?->format('M d, Y'),
            'land_owner' => $property->landOwner,
            'media' => $property->media,
        ];
    }
}
