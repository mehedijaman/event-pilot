<?php

use App\Enums\PaymentStatus;
use App\Mail\TicketMail;
use App\Models\Event;
use App\Models\Package;
use App\Models\PaymentMethod;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    PaymentMethod::create(['name' => 'bKash', 'slug' => 'bkash', 'account_type' => 'mobile', 'account_number' => '01XXXXXXXXX', 'is_active' => true]);

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

it('sends ticket email on payment verification', function () {
    Mail::fake();

    $admin = User::factory()->create(['role' => 'admin']);
    $registration = Registration::factory()->create([
        'event_id' => $this->event->id,
        'package_id' => $this->package->id,
        'email' => 'test@example.com',
        'payment_status' => PaymentStatus::Pending,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.registrations.verify', $registration));

    Mail::assertSent(TicketMail::class, function ($mail) {
        return $mail->hasTo('test@example.com');
    });

    $registration->refresh();
    expect($registration->ticket_email_sent_at)->not->toBeNull();
    expect($registration->payment_status)->toBe(PaymentStatus::Verified);
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
