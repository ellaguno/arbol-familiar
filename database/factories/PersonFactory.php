<?php

namespace Database\Factories;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'patronymic' => fake()->lastName(),
            'gender' => fake()->randomElement(['M', 'F']),
            'is_living' => true,
            'privacy_level' => 'community',
            'consent_status' => 'not_required',
            'created_by' => User::factory(),
        ];
    }

    public function deceased(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_living' => false,
            'death_year' => fake()->numberBetween(1950, 2020),
        ]);
    }

    public function heritage(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_ethnic_heritage' => true,
            'heritage_region' => fake()->randomElement(['region_1', 'region_2', 'region_3', 'region_4']),
        ]);
    }

    public function minor(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_minor' => true,
            'birth_year' => now()->year - 10,
        ]);
    }

    public function withBirthDate(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_year' => 1980,
            'birth_month' => 5,
            'birth_day' => 15,
            'birth_date' => '1980-05-15',
        ]);
    }
}
