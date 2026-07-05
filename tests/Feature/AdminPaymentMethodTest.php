<?php

use App\Models\PaymentMethod;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

describe('admin payment method management', function () {
    it('lists all payment methods', function () {
        PaymentMethod::create(['name' => 'bKash', 'slug' => 'bkash', 'is_active' => true]);
        PaymentMethod::create(['name' => 'Nagad', 'slug' => 'nagad', 'is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.payment-methods.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/payment-methods/Index'));
    });

    it('requires authentication', function () {
        $this->get(route('admin.payment-methods.index'))->assertRedirect(route('login'));
    });

    it('shows create form', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.payment-methods.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/payment-methods/Create'));
    });

    it('creates a payment method', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment-methods.store'), [
                'name' => 'bKash',
                'slug' => 'bkash',
                'account_type' => 'mobile',
                'account_number' => '01712345678',
                'instructions' => 'Send money to this number',
                'is_active' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payment_methods', ['slug' => 'bkash']);
    });

    it('validates required fields on create', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment-methods.store'), []);

        $response->assertSessionHasErrors(['name', 'slug']);
    });

    it('validates unique slug on create', function () {
        PaymentMethod::create(['name' => 'bKash', 'slug' => 'bkash', 'is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment-methods.store'), [
                'name' => 'bKash 2',
                'slug' => 'bkash',
            ]);

        $response->assertSessionHasErrors(['slug']);
    });

    it('shows edit form', function () {
        $method = PaymentMethod::create(['name' => 'bKash', 'slug' => 'bkash', 'is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.payment-methods.edit', $method));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('admin/payment-methods/Edit'));
    });

    it('updates a payment method', function () {
        $method = PaymentMethod::create(['name' => 'bKash', 'slug' => 'bkash', 'is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.payment-methods.update', $method), [
                'name' => 'bKash Updated',
                'slug' => 'bkash',
                'account_type' => 'mobile',
                'account_number' => '01999999999',
                'instructions' => 'Updated instructions',
                'is_active' => false,
            ]);

        $response->assertRedirect();
        $method->refresh();
        expect($method->name)->toBe('bKash Updated');
        expect($method->is_active)->toBeFalsy();
    });

    it('deletes a payment method', function () {
        $method = PaymentMethod::create(['name' => 'bKash', 'slug' => 'bkash', 'is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.payment-methods.destroy', $method));

        $response->assertRedirect();
        $this->assertDatabaseMissing('payment_methods', ['id' => $method->id]);
    });
});
