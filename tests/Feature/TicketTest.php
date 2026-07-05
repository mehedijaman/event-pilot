<?php

use App\Events\RegistrationCreated;
use App\Mail\TicketMail;
use App\Models\Event;
use App\Models\Package;
use App\Models\Registration;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->event = Event::factory()->create([
        'is_active' => true,
        'indoor_capacity' => 100,
        'registration_opens_at' => now()->subDay(),
        'registration_closes_at' => now()->addMonth(),
    ]);

    $this->package = Package::factory()->create([
        'event_id' => $this->event->id,
        'price' => 500,
        'requires_student_verification' => false,
        'is_active' => true,
    ]);
});

it('dispatches RegistrationCreated event directly', function () {
    $registration = Registration::factory()->make([
        'event_id' => $this->event->id,
        'package_id' => $this->package->id,
    ]);

    EventFacade::fakeFor(function () use ($registration) {
        RegistrationCreated::dispatch($registration);
        EventFacade::assertDispatched(RegistrationCreated::class);
    });
});

it('sends ticket email on registration', function () {
    Mail::fake();

    $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'seat_position' => 'indoor',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '01712345678',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN123',
        'amount' => 500,
    ]);

    Mail::assertSent(TicketMail::class, function ($mail) {
        return $mail->hasTo('test@example.com');
    });
});

it('updates ticket_email_sent_at on registration', function () {
    $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'seat_position' => 'indoor',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '01712345678',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN456',
        'amount' => 500,
    ]);

    $registration = Registration::first();
    expect($registration->ticket_email_sent_at)->not->toBeNull();
});

it('sends ticket email via resend endpoint', function () {
    Mail::fake();

    $registration = Registration::factory()->create([
        'event_id' => $this->event->id,
        'package_id' => $this->package->id,
        'email' => 'attendee@example.com',
    ]);

    $response = $this->post(route('tickets.resend'), [
        'email' => 'attendee@example.com',
    ]);

    $response->assertSessionHas('success');

    Mail::assertSent(TicketMail::class, function ($mail) use ($registration) {
        return $mail->hasTo($registration->email)
            && $mail->registration->id === $registration->id;
    });

    expect($registration->fresh()->ticket_email_sent_at)->not->toBeNull();
});

it('fails resend for unknown email', function () {
    $response = $this->post(route('tickets.resend'), [
        'email' => 'unknown@example.com',
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('fails resend without email field', function () {
    $response = $this->post(route('tickets.resend'), []);

    $response->assertSessionHasErrors(['email']);
});

it('resends to the latest registration for the given email', function () {
    Mail::fake();

    $old = Registration::factory()->create([
        'event_id' => $this->event->id,
        'package_id' => $this->package->id,
        'email' => 'same@example.com',
        'created_at' => now()->subDay(),
    ]);

    $latest = Registration::factory()->create([
        'event_id' => $this->event->id,
        'package_id' => $this->package->id,
        'email' => 'same@example.com',
        'created_at' => now(),
    ]);

    $this->post(route('tickets.resend'), ['email' => 'same@example.com']);

    Mail::assertSent(TicketMail::class, function ($mail) use ($latest) {
        return $mail->registration->id === $latest->id;
    });
});
