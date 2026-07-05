<?php

namespace App\Http\Controllers;

use App\Actions\TicketPdf;
use App\Mail\TicketMail;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Response;

class TicketController extends Controller
{
    public function show(string $ticketCode): Response
    {
        $registration = Registration::where('ticket_code', $ticketCode)
            ->with(['event', 'package'])
            ->firstOrFail();

        return inertia('TicketStatus', [
            'registration' => $registration,
        ]);
    }

    public function resend(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $registration = Registration::where('email', $validated['email'])
            ->with(['event', 'package'])
            ->latest()
            ->first();

        if (! $registration) {
            return back()->withErrors([
                'email' => 'No registration found with that email address.',
            ]);
        }

        $pdf = app(TicketPdf::class)->generate($registration);

        Mail::to($registration->email)
            ->send(new TicketMail($registration, $pdf));

        $registration->updateQuietly(['ticket_email_sent_at' => now()]);

        return back()->with('success', 'Ticket resent successfully.');
    }
}
