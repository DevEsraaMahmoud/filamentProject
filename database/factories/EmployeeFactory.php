<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'address' => fake()->address(),
            'date_hired' => fake()->dateTime()->format('Y-m-d'),
            'country_id' => Country::factory(),
            'state_id' => State::factory(),
            'city_id' => City::factory(),
            'status' => fake()->numberBetween(0, 1),
        ];
    }
}
