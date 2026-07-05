<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->scanner = User::factory()->create(['role' => 'scanner']);
    Setting::create([
        'site_name' => 'Event Ticket',
        'slogan' => 'Your Gateway to Amazing Events',
        'contact_email' => 'hello@event.test',
    ]);
});

it('shows general settings page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.settings.general'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/settings/General'));
});

it('shows contact settings page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.settings.contact'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/settings/Contact'));
});

it('shows smtp settings page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.settings.smtp'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/settings/Smtp'));
});

it('requires authentication for settings pages', function () {
    $this->get(route('admin.settings.general'))->assertRedirect(route('login'));
    $this->get(route('admin.settings.contact'))->assertRedirect(route('login'));
    $this->get(route('admin.settings.smtp'))->assertRedirect(route('login'));
});

it('denies scanner access to settings pages', function () {
    $this->actingAs($this->scanner)->get(route('admin.settings.general'))->assertForbidden();
    $this->actingAs($this->scanner)->get(route('admin.settings.contact'))->assertForbidden();
    $this->actingAs($this->scanner)->get(route('admin.settings.smtp'))->assertForbidden();
});

it('updates general settings', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.settings.general'))
        ->put(route('admin.settings.general.update'), [
            'site_name' => 'Updated Event',
            'slogan' => 'New Slogan',
        ])
        ->assertSessionHas('success');

    $settings = Setting::first();
    expect($settings->site_name)->toBe('Updated Event');
    expect($settings->slogan)->toBe('New Slogan');
});

it('uploads logo image', function () {
    $file = UploadedFile::fake()->image('logo.png', 200, 200);

    $this->actingAs($this->admin)
        ->put(route('admin.settings.general.update'), ['logo' => $file])
        ->assertSessionHas('success');

    $settings = Setting::first();
    expect($settings->logo)->not->toBeNull();
    expect($settings->logo_url)->not->toBeNull();
});

it('updates contact settings', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.settings.contact'))
        ->put(route('admin.settings.contact.update'), [
            'contact_email' => 'updated@event.test',
            'contact_phone' => '+8801711111111',
            'contact_address' => 'New Address',
        ])
        ->assertSessionHas('success');

    $settings = Setting::first();
    expect($settings->contact_email)->toBe('updated@event.test');
    expect($settings->contact_phone)->toBe('+8801711111111');
    expect($settings->contact_address)->toBe('New Address');
});

it('updates social links', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.settings.contact.update'), [
            'social_facebook' => 'https://facebook.com/test',
            'social_twitter' => 'https://twitter.com/test',
            'social_instagram' => 'https://instagram.com/test',
        ])
        ->assertSessionHas('success');

    $settings = Setting::first();
    expect($settings->social_facebook)->toBe('https://facebook.com/test');
    expect($settings->social_twitter)->toBe('https://twitter.com/test');
    expect($settings->social_instagram)->toBe('https://instagram.com/test');
});

it('updates smtp settings', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.settings.smtp.update'), [
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_username' => 'user@example.com',
            'smtp_password' => 'secret123',
            'smtp_encryption' => 'tls',
            'smtp_from_address' => 'noreply@event.test',
            'smtp_from_name' => 'Event System',
        ])
        ->assertSessionHas('success');

    $settings = Setting::first();
    expect($settings->smtp_host)->toBe('smtp.example.com');
    expect($settings->smtp_port)->toBe(587);
    expect($settings->smtp_username)->toBe('user@example.com');
    expect($settings->smtp_password)->toBe('secret123');
    expect($settings->smtp_encryption)->toBe('tls');
    expect($settings->smtp_from_address)->toBe('noreply@event.test');
    expect($settings->smtp_from_name)->toBe('Event System');
});

it('encrypts smtp password', function () {
    $this->actingAs($this->admin)->put(route('admin.settings.smtp.update'), [
        'smtp_password' => 'my-secret-password',
    ]);

    $settings = Setting::first();
    expect($settings->getRawOriginal('smtp_password'))->not->toBe('my-secret-password');
    expect($settings->smtp_password)->toBe('my-secret-password');
});

it('validates smtp port is integer', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.settings.smtp'))
        ->put(route('admin.settings.smtp.update'), ['smtp_port' => 'not-a-number'])
        ->assertSessionHasErrors(['smtp_port']);
});

it('validates social links are valid urls', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.settings.contact.update'), ['social_facebook' => 'not-a-url'])
        ->assertSessionHasErrors(['social_facebook']);
});

it('validates contact email format', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.settings.contact.update'), ['contact_email' => 'not-an-email'])
        ->assertSessionHasErrors(['contact_email']);
});

it('denies scanner from updating settings', function () {
    $this->actingAs($this->scanner)
        ->put(route('admin.settings.general.update'), ['site_name' => 'Hacked'])
        ->assertForbidden();

    $this->actingAs($this->scanner)
        ->put(route('admin.settings.contact.update'), ['contact_email' => 'hacked@test.com'])
        ->assertForbidden();

    $this->actingAs($this->scanner)
        ->put(route('admin.settings.smtp.update'), ['smtp_host' => 'evil.com'])
        ->assertForbidden();
});
