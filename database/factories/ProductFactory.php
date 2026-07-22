<?php

namespace Database\Factories;

use App\Enums\WarrantyStatus;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $warehouseDate = fake()->dateTimeBetween('-18 months', 'now');
        $months = fake()->randomElement([6, 12, 18, 24]);

        return [
            'product_code' => strtoupper(fake()->bothify('SP-####')),
            'name' => fake()->words(3, true),
            'imei' => fake()->unique()->numerify('###############'),
            'warehouse_date' => $warehouseDate,
            'warranty_months' => $months,
            'warranty_status' => WarrantyStatus::ACTIVE,
            'internal_note' => fake()->optional()->sentence(),
        ];
    }
}
