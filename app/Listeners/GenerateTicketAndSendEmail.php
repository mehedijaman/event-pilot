<?php

namespace App\Listeners;

use App\Actions\TicketPdf;
use App\Events\RegistrationCreated;
use App\Mail\TicketMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class GenerateTicketAndSendEmail implements ShouldQueue
{
    public function handle(RegistrationCreated $event): void
    {
        $registration = $event->registration;

        $pdf = app(TicketPdf::class)->generate($registration);

        Mail::to($registration->email)
            ->send(new TicketMail($registration, $pdf));

        $registration->updateQuietly(['ticket_email_sent_at' => now()]);
    }
}
