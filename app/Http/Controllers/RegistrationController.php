<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\SeatPosition;
use App\Http\Requests\StoreRegistrationRequest;
use App\Models\Event;
use App\Models\PaymentMethod;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class RegistrationController extends Controller
{
    public function create(): Response
    {
        $event = Event::where('is_active', true)
            ->with('packages')
            ->firstOrFail();

        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'account_type', 'account_number', 'instructions']);

        return inertia('Register', [
            'event' => $event,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['event_id'] = $this->resolveEventId();

        $registration = DB::transaction(function () use ($validated) {
            $event = Event::findOrFail($validated['event_id']);

            $capacityField = $validated['seat_position'] === SeatPosition::Indoor->value
                ? 'indoor_capacity'
                : 'outdoor_capacity';

            if ($event->$capacityField !== null) {
                $taken = Registration::where('event_id', $event->id)
                    ->where('seat_position', $validated['seat_position'])
                    ->where('payment_status', '!=', PaymentStatus::Rejected->value)
                    ->count();

                if ($taken >= $event->$capacityField) {
                    throw ValidationException::withMessages([
                        'seat_position' => 'That section is full — please choose the other position.',
                    ]);
                }
            }

            return Registration::create($validated);
        });

        return redirect()->route('tickets.show', $registration->ticket_code);
    }

    private function resolveEventId(): int
    {
        $event = Event::where('is_active', true)->firstOrFail();

        return $event->id;
    }
}
