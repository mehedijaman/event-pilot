<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class RegistrationController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status');

        $registrations = Registration::query()
            ->with(['event', 'package', 'verifier'])
            ->when($status && in_array($status, ['pending', 'verified', 'rejected']), fn ($q) => $q->where('payment_status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($r) => [
                'id' => $r->id,
                'ticket_code' => $r->ticket_code,
                'name' => $r->name,
                'email' => $r->email,
                'phone' => $r->phone,
                'seat_position' => $r->seat_position->value,
                'payment_method' => $r->payment_method->value,
                'transaction_id' => $r->transaction_id,
                'amount' => $r->amount,
                'payment_status' => $r->payment_status->value,
                'rejection_reason' => $r->rejection_reason,
                'verified_at' => $r->verified_at,
                'checked_in_at' => $r->checked_in_at,
                'created_at' => $r->created_at,
                'package' => ['name' => $r->package->name],
                'event' => ['name' => $r->event->name],
                'verifier' => $r->verifier ? ['name' => $r->verifier->name] : null,
            ]);

        return inertia('admin/Registrations', [
            'registrations' => $registrations,
            'filter' => $status ?: 'all',
        ]);
    }

    public function verify(Registration $registration): RedirectResponse
    {
        if (! request()->user()->isAdmin()) {
            abort(403);
        }

        if ($registration->payment_status !== PaymentStatus::Pending) {
            return back()->withErrors(['message' => 'Only pending registrations can be verified.']);
        }

        $registration->update([
            'payment_status' => PaymentStatus::Verified,
            'verified_by' => request()->user()->id,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Registration verified successfully.');
    }

    public function reject(Request $request, Registration $registration): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        if ($registration->payment_status !== PaymentStatus::Pending) {
            return back()->withErrors(['message' => 'Only pending registrations can be rejected.']);
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $registration->update([
            'payment_status' => PaymentStatus::Rejected,
            'rejection_reason' => $validated['rejection_reason'],
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Registration rejected.');
    }
}
