<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $name = fake()->sentence(3);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->randomNumber(4),
            'description' => fake()->paragraph(),
            'event_date' => fake()->dateTimeBetween('+1 month', '+3 months'),
            'venue' => fake()->address(),
            'indoor_capacity' => fake()->numberBetween(50, 500),
            'outdoor_capacity' => fake()->numberBetween(50, 500),
            'registration_opens_at' => now()->subWeek(),
            'registration_closes_at' => now()->addMonth(),
            'is_active' => true,
        ];
    }
}
