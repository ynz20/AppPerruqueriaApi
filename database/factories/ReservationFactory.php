<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => $this->faker->date(),
            'hour' => $this->faker->time(),
            'client_dni' => $this->faker->randomDigitNotNull(),
            'worker_dni' => $this->faker->randomDigitNotNull(),
            'service_id' => $this->faker->randomDigitNotNull(),
            'shift_id' => $this->faker->randomDigitNotNull(),
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled']),
        ];
    }
}
