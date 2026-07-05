<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Setting;

class PublicController extends Controller
{
    public function __invoke()
    {
        $event = Event::where('is_active', true)
            ->with('packages', 'media')
            ->first();

        $settings = Setting::first();

        return inertia('Landing', [
            'event' => $event ? [
                'name' => $event->name,
                'description' => $event->description,
                'event_date' => $event->event_date,
                'venue' => $event->venue,
                'cover_photo_url' => $event->getFirstMediaUrl('cover'),
                'packages' => $event->packages->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => $p->price,
                    'requires_student_verification' => $p->requires_student_verification,
                    'description' => $p->description,
                ]),
            ] : null,
            'settings' => $settings ? [
                'site_name' => $settings->site_name,
                'slogan' => $settings->slogan,
                'logo_url' => $settings->logo_url,
                'favicon_url' => $settings->favicon_url,
                'contact_email' => $settings->contact_email,
                'contact_phone' => $settings->contact_phone,
                'contact_address' => $settings->contact_address,
                'social_facebook' => $settings->social_facebook,
                'social_twitter' => $settings->social_twitter,
                'social_instagram' => $settings->social_instagram,
            ] : null,
        ]);
    }
}
