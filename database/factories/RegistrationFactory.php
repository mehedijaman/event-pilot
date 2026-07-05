<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Enums\SeatPosition;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        return [
            'ticket_code' => (string) Str::uuid(),
            'seat_position' => fake()->randomElement(SeatPosition::class),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '01'.fake()->numerify('##########'),
            'student_id_number' => null,
            'institution_name' => null,
            'payment_method' => fake()->randomElement(['bkash', 'nagad', 'rocket', 'bank', 'other']),
            'transaction_id' => Str::upper(Str::random(10)),
            'amount' => fake()->randomFloat(2, 200, 5000),
            'payment_status' => PaymentStatus::Pending,
            'verified_by' => null,
            'verified_at' => null,
            'rejection_reason' => null,
            'checked_in_at' => null,
            'checked_in_by' => null,
            'ticket_email_sent_at' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Verified,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Rejected,
            'rejection_reason' => 'Transaction ID not found.',
        ]);
    }
}
