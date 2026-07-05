<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class CheckInController extends Controller
{
    public function index(Request $request): Response
    {
        $registration = null;

        if ($request->filled('ticket_code')) {
            $registration = Registration::where('ticket_code', $request->ticket_code)
                ->with(['event', 'package'])
                ->first();
        }

        return inertia('admin/CheckIn', [
            'lookup' => $registration ? [
                'id' => $registration->id,
                'ticket_code' => $registration->ticket_code,
                'name' => $registration->name,
                'email' => $registration->email,
                'seat_position' => $registration->seat_position->value,
                'payment_status' => $registration->payment_status->value,
                'checked_in_at' => $registration->checked_in_at,
                'event' => ['name' => $registration->event->name],
                'package' => ['name' => $registration->package->name],
            ] : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_code' => ['required', 'string', 'max:255'],
        ]);

        $registration = Registration::where('ticket_code', $validated['ticket_code'])
            ->with(['event', 'package'])
            ->first();

        if (! $registration) {
            return back()->withErrors([
                'ticket_code' => 'No registration found with that ticket code.',
            ]);
        }

        if ($registration->payment_status !== PaymentStatus::Verified) {
            return back()->withErrors([
                'ticket_code' => 'Cannot check in: payment is not verified.',
            ]);
        }

        if ($registration->checked_in_at !== null) {
            return back()->withErrors([
                'ticket_code' => 'Already checked in at '.$registration->checked_in_at->format('d M Y, h:i A').'.',
            ]);
        }

        $registration->update([
            'checked_in_at' => now(),
            'checked_in_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Check-in successful for '.$registration->name.'.');
    }
}
