<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::where('role', 'client')->inRandomOrder()->first()->id ?? 1,
            'pharmacy_id' => \App\Models\Pharmacy::inRandomOrder()->first()->id ?? 1,
            'status' => $this->faker->randomElement(['pending', 'preparing', 'delivering', 'completed']),
            'total_price' => $this->faker->randomFloat(2, 5, 200),
        ];
    }
}
