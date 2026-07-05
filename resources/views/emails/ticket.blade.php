<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1b1b18; }
        .container { max-width: 560px; margin: 0 auto; padding: 24px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .subtitle { color: #706f6c; font-size: 14px; margin-bottom: 24px; }
        .details { background: #f5f5f4; border-radius: 8px; padding: 16px; margin-bottom: 24px; }
        .details dt { font-size: 12px; color: #706f6c; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 12px; }
        .details dt:first-child { margin-top: 0; }
        .details dd { font-size: 14px; margin: 2px 0 0 0; font-weight: 500; }
        .status { display: inline-block; background: #fef3c7; color: #92400e; padding: 2px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .footer { font-size: 12px; color: #706f6c; border-top: 1px solid #e3e3e0; padding-top: 16px; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registration Confirmed</h1>
        <p class="subtitle">Your ticket is attached to this email.</p>

        <div class="details">
            <dl>
                <dt>Event</dt>
                <dd>{{ $registration->event->name }}</dd>

                <dt>Ticket Code</dt>
                <dd style="font-family: monospace;">{{ $registration->ticket_code }}</dd>

                <dt>Name</dt>
                <dd>{{ $registration->name }}</dd>

                <dt>Package</dt>
                <dd>{{ $registration->package->name }} — ৳{{ number_format($registration->amount, 2) }}</dd>

                <dt>Seat Position</dt>
                <dd style="text-transform: capitalize">{{ $registration->seat_position->value }}</dd>

                <dt>Status</dt>
                <dd><span class="status">Pending Verification</span></dd>
            </dl>
        </div>

        <p style="font-size: 14px;">
            Your payment is being verified. You will receive another email once it has been confirmed.
        </p>

        @if ($registration->event->venue)
            <p style="font-size: 14px;">
                <strong>Venue:</strong> {{ $registration->event->venue }}<br>
                <strong>Date:</strong> {{ $registration->event->event_date->format('l, F j, Y') }}
            </p>
        @endif

        <div class="footer">
            <p>If you have any questions, please contact the event organizers.</p>
            <p>To check your ticket status, visit: {{ route('tickets.show', $registration->ticket_code) }}</p>
        </div>
    </div>
</body>
</html>
