<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 20px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1b1b18; }
        .ticket { max-width: 100%; padding: 16px; }
        h1 { font-size: 18px; margin: 0 0 4px 0; }
        .meta { margin-bottom: 12px; }
        .meta dt { font-size: 9px; color: #706f6c; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 8px; }
        .meta dd { margin: 2px 0 0 0; font-weight: 600; font-size: 13px; }
        .qr { text-align: center; margin: 16px 0; }
        .qr img { display: inline-block; }
        .ticket-code { text-align: center; font-family: monospace; font-size: 10px; color: #706f6c; margin-top: 4px; }
        .footer { text-align: center; font-size: 8px; color: #706f6c; margin-top: 12px; border-top: 1px solid #e3e3e0; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="ticket">
        <h1>{{ $registration->event->name }}</h1>

        <dl class="meta">
            <dt>Attendee</dt>
            <dd>{{ $registration->name }}</dd>

            <dt>Package</dt>
            <dd>{{ $registration->package->name }} — ৳{{ number_format($registration->amount, 2) }}</dd>

            <dt>Seat</dt>
            <dd style="text-transform: capitalize">{{ $registration->seat_position->value }}</dd>

            <dt>Date</dt>
            <dd>{{ $registration->event->event_date->format('l, F j, Y') }}</dd>

            @if ($registration->event->venue)
                <dt>Venue</dt>
                <dd>{{ $registration->event->venue }}</dd>
            @endif
        </dl>

        <div class="qr">
            <img src="{{ $qrCode }}" alt="QR Code" width="180" height="180" />
        </div>
        <div class="ticket-code">{{ $registration->ticket_code }}</div>

        <div class="footer">
            <p>Present this ticket (digital or printed) at the entrance for scanning.</p>
        </div>
    </div>
</body>
</html>
