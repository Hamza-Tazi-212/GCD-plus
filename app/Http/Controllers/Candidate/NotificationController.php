<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('candidate.notifications', [
            'notifications' => $request->user()->notifications()->orderByDesc('created_at')->orderByDesc('id')->paginate(15),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $entry = $request->user()->notifications()->findOrFail($notification);
        $entry->markAsRead();
        $application = $request->user()->applications()->find($entry->data['application_id'] ?? null);
        if (! $application) {
            return to_route('candidate.notifications.index');
        }

        return match ($entry->data['destination'] ?? null) {
            'renewal' => to_route('renewals.index'),
            'enrolment' => $application->status === \App\Enums\ApplicationStatus::Accepted
                ? to_route('enrolment-documents.show', $application)
                : to_route('candidate.applications.show', $application),
            default => to_route('candidate.applications.show', $application),
        };
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return to_route('candidate.notifications.index');
    }
}
