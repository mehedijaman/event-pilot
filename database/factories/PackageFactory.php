<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Standard', 'Premium', 'VIP', 'Student', 'Early Bird']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'price' => fake()->randomFloat(2, 200, 5000),
            'requires_student_verification' => false,
            'description' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
