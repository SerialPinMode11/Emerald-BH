<?php

namespace App\Http\Controllers;

use App\Models\ChangeRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DevAdminController extends Controller
{
    public function dashboard(): Response
    {
        $requests = ChangeRequest::query()
            ->with('requester:id,name')
            ->latest()
            ->get();

        return Inertia::render('emerald/dev-admin/Dashboard', [
            'stats' => [
                'pending' => $requests->where('status', 'pending')->count(),
                'in_progress' => $requests->where('status', 'in_progress')->count(),
                'deployed' => $requests->where('status', 'deployed')->count(),
                'critical' => $requests->where('priority', 'critical')->whereNot('status', 'deployed')->count(),
            ],
            'changeRequests' => $requests->map(fn (ChangeRequest $cr) => [
                'id' => $cr->id,
                'request_type' => $cr->request_type,
                'description' => $cr->description,
                'priority' => $cr->priority,
                'status' => $cr->status,
                'dev_admin_note' => $cr->dev_admin_note,
                'requester' => $cr->requester,
                'created_at' => $cr->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function updateChangeRequest(Request $request, ChangeRequest $changeRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,deployed,rejected'],
            'dev_admin_note' => ['nullable', 'string'],
        ]);

        $data = $validated;
        if ($validated['status'] === 'deployed') {
            $data['deployed_at'] = now();
        }

        $changeRequest->update($data);
        AuditLogger::log('change_request_updated', $changeRequest, $request->user());

        return back()->with('success', 'Change request updated.');
    }

    public function deploy(ChangeRequest $changeRequest): RedirectResponse
    {
        $changeRequest->update([
            'status' => 'deployed',
            'deployed_at' => now(),
        ]);

        AuditLogger::log('change_request_deployed', $changeRequest, request()->user());

        return back()->with('success', 'Marked as deployed.');
    }
}
