<?php

use App\Models\Event;
use App\Models\Package;
use App\Models\Registration;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->scanner = User::factory()->create(['role' => 'scanner']);
});

it('lists events', function () {
    Event::factory(3)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.events.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/events/Index'));
});

it('requires authentication for events page', function () {
    $this->get(route('admin.events.index'))->assertRedirect(route('login'));
});

it('denies scanner access to events page', function () {
    $this->actingAs($this->scanner)
        ->get(route('admin.events.index'))
        ->assertForbidden();
});

it('shows create form', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.events.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/events/Create'));
});

it('creates event with packages', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.events.store'), [
            'name' => 'New Event',
            'description' => 'Event description',
            'event_date' => now()->addMonth()->format('Y-m-d\TH:i'),
            'venue' => 'Test Venue',
            'indoor_capacity' => 100,
            'outdoor_capacity' => 50,
            'registration_opens_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'registration_closes_at' => now()->addMonth()->format('Y-m-d\TH:i'),
            'is_active' => '1',
            'packages' => [
                ['name' => 'Standard', 'price' => 500, 'requires_student_verification' => false],
                ['name' => 'VIP', 'price' => 1000, 'requires_student_verification' => false],
            ],
        ])
        ->assertRedirect(route('admin.events.index'));

    $event = Event::where('name', 'New Event')->first();
    expect($event)->not->toBeNull();
    expect($event->slug)->toBe('new-event');
    expect($event->venue)->toBe('Test Venue');
    expect($event->packages)->toHaveCount(2);
    expect($event->packages->first()->name)->toBe('Standard');
});

it('validates required fields on create', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.events.store'), [])
        ->assertSessionHasErrors(['name', 'event_date', 'venue', 'indoor_capacity', 'outdoor_capacity', 'registration_opens_at', 'registration_closes_at']);
});

it('shows edit form', function () {
    $event = Event::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.events.edit', $event))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/events/Edit'));
});

it('updates event', function () {
    $event = Event::factory()->create([
        'name' => 'Old Name',
        'indoor_capacity' => 100,
        'outdoor_capacity' => 50,
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.events.update', $event), [
            'name' => 'Updated Name',
            'description' => $event->description,
            'event_date' => $event->event_date->format('Y-m-d\TH:i'),
            'venue' => $event->venue,
            'indoor_capacity' => $event->indoor_capacity,
            'outdoor_capacity' => $event->outdoor_capacity,
            'registration_opens_at' => $event->registration_opens_at->format('Y-m-d\TH:i'),
            'registration_closes_at' => $event->registration_closes_at->format('Y-m-d\TH:i'),
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.events.index'));

    expect($event->fresh()->name)->toBe('Updated Name');
    expect($event->fresh()->slug)->toBe('updated-name');
});

it('deletes event without registrations', function () {
    $event = Event::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.events.destroy', $event))
        ->assertRedirect(route('admin.events.index'));

    expect(Event::find($event->id))->toBeNull();
});

it('prevents deleting event with registrations', function () {
    $event = Event::factory()->create();
    Registration::factory()->create([
        'event_id' => $event->id,
        'package_id' => Package::factory()->create(['event_id' => $event->id])->id,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.events.destroy', $event))
        ->assertSessionHasErrors(['message']);

    expect(Event::find($event->id))->not->toBeNull();
});
