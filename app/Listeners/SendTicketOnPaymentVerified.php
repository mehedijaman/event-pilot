<?php

namespace App\Listeners;

use App\Actions\TicketPdf;
use App\Events\PaymentVerified;
use App\Mail\TicketMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendTicketOnPaymentVerified implements ShouldQueue
{
    public function handle(PaymentVerified $event): void
    {
        $registration = $event->registration;

        $pdf = app(TicketPdf::class)->generate($registration);

        Mail::to($registration->email)
            ->send(new TicketMail($registration, $pdf));

        $registration->updateQuietly(['ticket_email_sent_at' => now()]);
    }
}
