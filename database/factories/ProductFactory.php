<?php
namespace Database\Factories;

use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $pharmacy = Pharmacy::inRandomOrder()->first();

        return [
            'name' => $this->faker->words(2, true),
            'price' => $this->faker->randomFloat(2, 2, 50),
            'stock' => $this->faker->numberBetween(0, 100),
            'pharmacy_id' => $pharmacy ? $pharmacy->id : Pharmacy::factory(),
        ];
    }
}
