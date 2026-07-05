<?php

namespace App\Http\Controllers;

use App\Models\Event;

class PublicController extends Controller
{
    public function __invoke()
    {
        $event = Event::where('is_active', true)
            ->with('packages')
            ->first();

        return inertia('Landing', [
            'event' => $event,
        ]);
    }
}
