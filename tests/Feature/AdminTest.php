<?php

use App\Enums\PaymentStatus;
use App\Models\Event;
use App\Models\Package;
use App\Models\Registration;
use App\Models\User;

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
        'is_active' => true,
    ]);

    $this->admin = User::factory()->create(['role' => 'admin']);
});

describe('admin registration management', function () {
    it('lists all registrations', function () {
        Registration::factory(3)->create([
            'event_id' => $this->event->id,
            'package_id' => $this->package->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.registrations.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/Registrations'));
    });

    it('requires authentication for registrations page', function () {
        $this->get(route('admin.registrations.index'))->assertRedirect(route('login'));
    });

    it('verifies pending registration', function () {
        $registration = Registration::factory()->create([
            'event_id' => $this->event->id,
            'package_id' => $this->package->id,
            'payment_status' => PaymentStatus::Pending,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.registrations.verify', $registration));

        $response->assertSessionHas('success');

        $registration->refresh();
        expect($registration->payment_status->value)->toBe('verified');
        expect($registration->verified_by)->toBe($this->admin->id);
        expect($registration->verified_at)->not->toBeNull();
    });

    it('rejects pending registration', function () {
        $registration = Registration::factory()->create([
            'event_id' => $this->event->id,
            'package_id' => $this->package->id,
            'payment_status' => PaymentStatus::Pending,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.registrations.reject', $registration), [
                'rejection_reason' => 'Invalid transaction ID.',
            ]);

        $response->assertSessionHas('success');

        $registration->refresh();
        expect($registration->payment_status->value)->toBe('rejected');
        expect($registration->rejection_reason)->toBe('Invalid transaction ID.');
        expect($registration->verified_by)->toBe($this->admin->id);
        expect($registration->verified_at)->not->toBeNull();
    });

    it('rejects verify for non-pending registration', function () {
        $registration = Registration::factory()->create([
            'event_id' => $this->event->id,
            'package_id' => $this->package->id,
            'payment_status' => PaymentStatus::Verified,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.registrations.verify', $registration));

        $response->assertSessionHasErrors(['message']);
    });

    it('rejects reject for non-pending registration', function () {
        $registration = Registration::factory()->create([
            'event_id' => $this->event->id,
            'package_id' => $this->package->id,
            'payment_status' => PaymentStatus::Verified,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.registrations.reject', $registration), [
                'rejection_reason' => 'Some reason.',
            ]);

        $response->assertSessionHasErrors(['message']);
    });

    it('rejects verify for scanner user', function () {
        $scanner = User::factory()->create(['role' => 'scanner']);
        $registration = Registration::factory()->create([
            'event_id' => $this->event->id,
            'package_id' => $this->package->id,
            'payment_status' => PaymentStatus::Pending,
        ]);

        $response = $this->actingAs($scanner)
            ->post(route('admin.registrations.verify', $registration));

        $response->assertForbidden();
    });
});

describe('check-in', function () {
    it('shows check-in page', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.check-in.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/CheckIn'));
    });

    it('requires authentication for check-in page', function () {
        $this->get(route('admin.check-in.index'))->assertRedirect(route('login'));
    });

    it('checks in verified attendee', function () {
        $registration = Registration::factory()->create([
            'event_id' => $this->event->id,
            'package_id' => $this->package->id,
            'payment_status' => PaymentStatus::Verified,
            'checked_in_at' => null,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.check-in.store'), [
                'ticket_code' => $registration->ticket_code,
            ]);

        $response->assertSessionHas('success');

        $registration->refresh();
        expect($registration->checked_in_at)->not->toBeNull();
        expect($registration->checked_in_by)->toBe($this->admin->id);
    });

    it('fails check-in for unverified registration', function () {
        $registration = Registration::factory()->create([
            'event_id' => $this->event->id,
            'package_id' => $this->package->id,
            'payment_status' => PaymentStatus::Pending,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.check-in.store'), [
                'ticket_code' => $registration->ticket_code,
            ]);

        $response->assertSessionHasErrors(['ticket_code']);
    });

    it('fails check-in for already checked in attendee', function () {
        $registration = Registration::factory()->create([
            'event_id' => $this->event->id,
            'package_id' => $this->package->id,
            'payment_status' => PaymentStatus::Verified,
            'checked_in_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.check-in.store'), [
                'ticket_code' => $registration->ticket_code,
            ]);

        $response->assertSessionHasErrors(['ticket_code']);
    });

    it('fails check-in for invalid ticket code', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.check-in.store'), [
                'ticket_code' => 'nonexistent-code',
            ]);

        $response->assertSessionHasErrors(['ticket_code']);
    });

    it('fails check-in without ticket code', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.check-in.store'), []);

        $response->assertSessionHasErrors(['ticket_code']);
    });

    it('allows scanner to check in', function () {
        $scanner = User::factory()->create(['role' => 'scanner']);
        $registration = Registration::factory()->create([
            'event_id' => $this->event->id,
            'package_id' => $this->package->id,
            'payment_status' => PaymentStatus::Verified,
        ]);

        $response = $this->actingAs($scanner)
            ->post(route('admin.check-in.store'), [
                'ticket_code' => $registration->ticket_code,
            ]);

        $response->assertSessionHas('success');
    });
});
