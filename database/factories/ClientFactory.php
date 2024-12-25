<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dni' => $this->faker->unique()->regexify('[0-9]{8}[A-Z]'),
            'name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'telf' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            
        ];
    }
}
