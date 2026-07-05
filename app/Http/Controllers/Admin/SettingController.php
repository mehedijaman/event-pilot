<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;

class SettingController extends Controller
{
    public function editGeneral(): Response
    {
        if (! request()->user()?->isAdmin()) {
            abort(403);
        }

        $settings = Setting::first();

        return inertia('admin/settings/General', [
            'settings' => $settings ? [
                'site_name' => $settings->site_name,
                'slogan' => $settings->slogan,
                'logo_url' => $settings->logo_url,
                'favicon_url' => $settings->favicon_url,
            ] : null,
        ]);
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'slogan' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,ico,svg', 'max:1024'],
        ]);

        $settings = Setting::firstOrCreate([]);
        $data = [];

        if (array_key_exists('site_name', $validated)) {
            $data['site_name'] = $validated['site_name'];
        }
        if (array_key_exists('slogan', $validated)) {
            $data['slogan'] = $validated['slogan'];
        }

        if ($request->hasFile('logo')) {
            if ($settings->logo) {
                Storage::disk('public')->delete($settings->logo);
            }
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($settings->favicon) {
                Storage::disk('public')->delete($settings->favicon);
            }
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
        }

        $settings->update($data);

        return back()->with('success', 'General settings updated.');
    }

    public function editContact(): Response
    {
        if (! request()->user()?->isAdmin()) {
            abort(403);
        }

        $settings = Setting::first();

        return inertia('admin/settings/Contact', [
            'settings' => $settings ? [
                'contact_email' => $settings->contact_email,
                'contact_phone' => $settings->contact_phone,
                'contact_address' => $settings->contact_address,
                'social_facebook' => $settings->social_facebook,
                'social_twitter' => $settings->social_twitter,
                'social_instagram' => $settings->social_instagram,
            ] : null,
        ]);
    }

    public function updateContact(Request $request): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_address' => ['nullable', 'string', 'max:1000'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_twitter' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
        ]);

        $settings = Setting::firstOrCreate([]);
        $settings->update($validated);

        return back()->with('success', 'Contact settings updated.');
    }

    public function editSmtp(): Response
    {
        if (! request()->user()?->isAdmin()) {
            abort(403);
        }

        $settings = Setting::first();

        return inertia('admin/settings/Smtp', [
            'settings' => $settings ? [
                'smtp_host' => $settings->smtp_host,
                'smtp_port' => $settings->smtp_port,
                'smtp_username' => $settings->smtp_username,
                'smtp_encryption' => $settings->smtp_encryption,
                'smtp_from_address' => $settings->smtp_from_address,
                'smtp_from_name' => $settings->smtp_from_name,
            ] : null,
        ]);
    }

    public function updateSmtp(Request $request): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'min:6'],
            'smtp_encryption' => ['nullable', 'string', 'in:tls,ssl'],
            'smtp_from_address' => ['nullable', 'email', 'max:255'],
            'smtp_from_name' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = Setting::firstOrCreate([]);
        $data = $validated;

        if (! $request->filled('smtp_password')) {
            unset($data['smtp_password']);
        }

        $settings->update($data);

        return back()->with('success', 'SMTP settings updated.');
    }
}
