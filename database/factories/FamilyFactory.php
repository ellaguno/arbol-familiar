<?php

namespace Database\Factories;

use App\Models\Family;
use Illuminate\Database\Eloquent\Factories\Factory;

class FamilyFactory extends Factory
{
    protected $model = Family::class;

    public function definition(): array
    {
        return [
            'status' => 'married',
        ];
    }

    public function divorced(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'divorced',
            'divorce_date' => fake()->date(),
        ]);
    }
}
