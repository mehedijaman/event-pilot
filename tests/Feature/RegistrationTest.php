<?php

use App\Models\Event;
use App\Models\Package;
use App\Models\PaymentMethod;
use App\Models\Registration;

beforeEach(function () {
    PaymentMethod::create(['name' => 'bKash', 'slug' => 'bkash', 'account_type' => 'mobile', 'account_number' => '01XXXXXXXXX', 'is_active' => true]);
    PaymentMethod::create(['name' => 'Nagad', 'slug' => 'nagad', 'account_type' => 'mobile', 'account_number' => '01XXXXXXXXX', 'is_active' => true]);

    $this->event = Event::factory()->create([
        'is_active' => true,
        'indoor_capacity' => 10,
        'outdoor_capacity' => null,
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

it('renders the registration page', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Register'));
});

it('rejects a registration with missing fields', function () {
    $response = $this->post(route('register.store'), []);

    $response->assertSessionHasErrors(['package_id', 'quantity', 'seat_position', 'name', 'email', 'phone', 'payment_method', 'transaction_id', 'amount']);
});

it('rejects a registration with invalid phone number', function () {
    $response = $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 1,
        'seat_position' => 'indoor',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '12345',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN123',
        'amount' => 500,
    ]);

    $response->assertSessionHasErrors(['phone']);
});

it('rejects a registration with invalid seat position', function () {
    $response = $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 1,
        'seat_position' => 'rooftop',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '01712345678',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN123',
        'amount' => 500,
    ]);

    $response->assertSessionHasErrors(['seat_position']);
});

it('rejects a registration with invalid payment method', function () {
    $response = $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 1,
        'seat_position' => 'indoor',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '01712345678',
        'payment_method' => 'credit_card',
        'transaction_id' => 'TXN123',
        'amount' => 500,
    ]);

    $response->assertSessionHasErrors(['payment_method']);
});

it('creates a registration successfully', function () {
    $response = $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 1,
        'seat_position' => 'indoor',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '01712345678',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN123',
        'amount' => 500,
    ]);

    $response->assertRedirect();

    $registration = Registration::first();
    expect($registration)->not->toBeNull();
    expect($registration->payment_status->value)->toBe('pending');
    expect($registration->ticket_code)->not->toBeNull();
    expect($registration->name)->toBe('Test User');
    expect($registration->email)->toBe('test@example.com');
    expect($registration->quantity)->toBe(1);
});

it('redirects to ticket page after registration', function () {
    $response = $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 1,
        'seat_position' => 'indoor',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '01712345678',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN123',
        'amount' => 500,
    ]);

    $registration = Registration::first();

    $response->assertRedirect(route('tickets.show', $registration->ticket_code));
});

it('prevents duplicate transaction ids', function () {
    $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 1,
        'seat_position' => 'indoor',
        'name' => 'User One',
        'email' => 'one@example.com',
        'phone' => '01712345678',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN123',
        'amount' => 500,
    ]);

    $response = $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 1,
        'seat_position' => 'outdoor',
        'name' => 'User Two',
        'email' => 'two@example.com',
        'phone' => '01712345679',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN123',
        'amount' => 500,
    ]);

    $response->assertSessionHasErrors(['transaction_id']);
});

it('allows same transaction id for different payment methods', function () {
    $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 1,
        'seat_position' => 'indoor',
        'name' => 'User One',
        'email' => 'one@example.com',
        'phone' => '01712345678',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN123',
        'amount' => 500,
    ]);

    $response = $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 1,
        'seat_position' => 'outdoor',
        'name' => 'User Two',
        'email' => 'two@example.com',
        'phone' => '01712345679',
        'payment_method' => 'nagad',
        'transaction_id' => 'TXN123',
        'amount' => 500,
    ]);

    $response->assertRedirect();
});

it('requires student id for student packages', function () {
    $studentPackage = Package::factory()->create([
        'event_id' => $this->event->id,
        'requires_student_verification' => true,
        'is_active' => true,
    ]);

    $response = $this->post(route('register.store'), [
        'package_id' => $studentPackage->id,
        'quantity' => 1,
        'seat_position' => 'indoor',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '01712345678',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN456',
        'amount' => $studentPackage->price,
    ]);

    $response->assertSessionHasErrors(['student_id_number']);
});

it('enforces indoor capacity limit', function () {
    $this->event->update(['indoor_capacity' => 1]);

    $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 1,
        'seat_position' => 'indoor',
        'name' => 'User One',
        'email' => 'one@example.com',
        'phone' => '01712345678',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN001',
        'amount' => 500,
    ]);

    $response = $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 1,
        'seat_position' => 'indoor',
        'name' => 'User Two',
        'email' => 'two@example.com',
        'phone' => '01712345679',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN002',
        'amount' => 500,
    ]);

    $response->assertSessionHasErrors(['seat_position']);
});

it('shows ticket status page', function () {
    $registration = Registration::factory()->create([
        'event_id' => $this->event->id,
        'package_id' => $this->package->id,
    ]);

    $response = $this->get(route('tickets.show', $registration->ticket_code));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('TicketStatus'));
});

it('rejects invalid quantity values', function () {
    $response = $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 0,
        'seat_position' => 'indoor',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '01712345678',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN123',
        'amount' => 500,
    ]);

    $response->assertSessionHasErrors(['quantity']);

    $response = $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 7,
        'seat_position' => 'indoor',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '01712345678',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN456',
        'amount' => 3500,
    ]);

    $response->assertSessionHasErrors(['quantity']);
});

it('creates a group registration with quantity 3', function () {
    $response = $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 3,
        'seat_position' => 'indoor',
        'name' => 'Group Leader',
        'email' => 'leader@example.com',
        'phone' => '01712345678',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN789',
        'amount' => 1500,
    ]);

    $response->assertRedirect();

    $registration = Registration::first();
    expect($registration->quantity)->toBe(3);
    expect($registration->amount)->toBe('1500.00');
});

it('rejects amount mismatch for group registration', function () {
    $response = $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 3,
        'seat_position' => 'indoor',
        'name' => 'Group Leader',
        'email' => 'leader@example.com',
        'phone' => '01712345678',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN789',
        'amount' => 500,
    ]);

    $response->assertSessionHasErrors(['amount']);
});

it('accounts for quantity in capacity check', function () {
    $this->event->update(['indoor_capacity' => 5]);

    $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 3,
        'seat_position' => 'indoor',
        'name' => 'User One',
        'email' => 'one@example.com',
        'phone' => '01712345678',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN001',
        'amount' => 1500,
    ]);

    $response = $this->post(route('register.store'), [
        'package_id' => $this->package->id,
        'quantity' => 3,
        'seat_position' => 'indoor',
        'name' => 'User Two',
        'email' => 'two@example.com',
        'phone' => '01712345679',
        'payment_method' => 'bkash',
        'transaction_id' => 'TXN002',
        'amount' => 1500,
    ]);

    $response->assertSessionHasErrors(['seat_position']);
});

it('passes maxQuantity to registration page', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('maxQuantity'));
});
