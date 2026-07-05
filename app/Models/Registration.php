<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SeatPosition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'seat_position' => SeatPosition::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'verified_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'ticket_email_sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Registration $registration) {
            $registration->ticket_code ??= (string) Str::uuid();
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
