<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        if (! request()->user()?->isAdmin()) {
            abort(403);
        }

        $events = Event::query()
            ->withCount('packages', 'registrations')
            ->with('media')
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'slug' => $e->slug,
                'event_date' => $e->event_date,
                'venue' => $e->venue,
                'is_active' => $e->is_active,
                'indoor_capacity' => $e->indoor_capacity,
                'outdoor_capacity' => $e->outdoor_capacity,
                'registration_opens_at' => $e->registration_opens_at,
                'registration_closes_at' => $e->registration_closes_at,
                'packages_count' => $e->packages_count,
                'registrations_count' => $e->registrations_count,
                'created_at' => $e->created_at,
                'cover_photo_url' => $e->getFirstMediaUrl('cover'),
            ]);

        return inertia('admin/events/Index', ['events' => $events]);
    }

    public function create(): Response
    {
        if (! request()->user()?->isAdmin()) {
            abort(403);
        }

        return inertia('admin/events/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'event_date' => ['required', 'date'],
            'venue' => ['required', 'string', 'max:255'],
            'indoor_capacity' => ['required', 'integer', 'min:0'],
            'outdoor_capacity' => ['required', 'integer', 'min:0'],
            'registration_opens_at' => ['required', 'date'],
            'registration_closes_at' => ['required', 'date', 'after:registration_opens_at'],
            'is_active' => ['boolean'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        $event = Event::create(Arr::except($validated, ['cover_photo']));

        if ($request->hasFile('cover_photo')) {
            $event->addMediaFromRequest('cover_photo')->toMediaCollection('cover');
        }

        $packages = $request->validate([
            'packages' => ['nullable', 'array'],
            'packages.*.name' => ['required', 'string', 'max:255'],
            'packages.*.price' => ['required', 'numeric', 'min:0'],
            'packages.*.requires_student_verification' => ['boolean'],
            'packages.*.description' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach (($packages['packages'] ?? []) as $i => $pkg) {
            $event->packages()->create([
                'name' => $pkg['name'],
                'slug' => Str::slug($pkg['name']),
                'price' => $pkg['price'],
                'requires_student_verification' => $pkg['requires_student_verification'] ?? false,
                'description' => $pkg['description'] ?? null,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        return to_route('admin.events.index')->with('success', 'Event created successfully.');
    }

    public function edit(Event $event): Response
    {
        $event->load('packages');

        return inertia('admin/events/Edit', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'description' => $event->description,
                'event_date' => $event->event_date,
                'venue' => $event->venue,
                'indoor_capacity' => $event->indoor_capacity,
                'outdoor_capacity' => $event->outdoor_capacity,
                'registration_opens_at' => $event->registration_opens_at,
                'registration_closes_at' => $event->registration_closes_at,
                'is_active' => $event->is_active,
                'cover_photo_url' => $event->getFirstMediaUrl('cover'),
                'packages' => $event->packages->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => (float) $p->price,
                    'requires_student_verification' => $p->requires_student_verification,
                    'description' => $p->description,
                    'sort_order' => $p->sort_order,
                    'is_active' => $p->is_active,
                ])->toArray(),
            ],
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'event_date' => ['required', 'date'],
            'venue' => ['required', 'string', 'max:255'],
            'indoor_capacity' => ['required', 'integer', 'min:0'],
            'outdoor_capacity' => ['required', 'integer', 'min:0'],
            'registration_opens_at' => ['required', 'date'],
            'registration_closes_at' => ['required', 'date', 'after:registration_opens_at'],
            'is_active' => ['boolean'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        $event->update(Arr::except($validated, ['cover_photo']));

        if ($request->hasFile('cover_photo')) {
            $event->clearMediaCollection('cover');
            $event->addMediaFromRequest('cover_photo')->toMediaCollection('cover');
        }

        $packages = $request->validate([
            'packages' => ['nullable', 'array'],
            'packages.*.id' => ['nullable', 'integer', 'exists:packages,id'],
            'packages.*.name' => ['required', 'string', 'max:255'],
            'packages.*.price' => ['required', 'numeric', 'min:0'],
            'packages.*.requires_student_verification' => ['boolean'],
            'packages.*.description' => ['nullable', 'string', 'max:1000'],
            'packages.*.is_active' => ['boolean'],
        ]);

        $submittedIds = collect($packages['packages'] ?? [])->pluck('id')->filter();
        $event->packages()->whereNotIn('id', $submittedIds)->delete();

        foreach (($packages['packages'] ?? []) as $i => $pkg) {
            $data = [
                'name' => $pkg['name'],
                'slug' => Str::slug($pkg['name']),
                'price' => $pkg['price'],
                'requires_student_verification' => $pkg['requires_student_verification'] ?? false,
                'description' => $pkg['description'] ?? null,
                'is_active' => $pkg['is_active'] ?? true,
                'sort_order' => $i,
            ];

            if (! empty($pkg['id'])) {
                $event->packages()->where('id', $pkg['id'])->update($data);
            } else {
                $event->packages()->create($data);
            }
        }

        return to_route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        if (! request()->user()?->isAdmin()) {
            abort(403);
        }

        if ($event->registrations()->exists()) {
            return back()->withErrors(['message' => 'Cannot delete event with existing registrations.']);
        }

        $event->packages()->delete();
        $event->delete();

        return to_route('admin.events.index')->with('success', 'Event deleted successfully.');
    }
}
