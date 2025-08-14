<?php

namespace Database\Factories;

use App\Models\Pharmacy;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $category = ProductCategory::inRandomOrder()->first();
        $pharmacy = Pharmacy::inRandomOrder()->first();

        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 2, 50),
            'stock' => $this->faker->numberBetween(0, 100),
            'category_id' => $category ? $category->id : ProductCategory::factory(),
            'pharmacy_id' => $pharmacy ? $pharmacy->id : Pharmacy::factory(),
        ];
    }
}
