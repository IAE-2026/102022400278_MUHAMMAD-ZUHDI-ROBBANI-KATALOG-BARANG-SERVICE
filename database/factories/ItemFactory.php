<?php

namespace Database\Factories;

use App\Enums\ItemStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-2 days', '+2 days');
        $basePrice = fake()->numberBetween(500_000, 20_000_000);

        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(16),
            'base_price' => $basePrice,
            'current_price' => $basePrice,
            'auction_start_at' => $start,
            'auction_end_at' => fake()->dateTimeBetween($start, '+10 days'),
            'status' => fake()->randomElement(ItemStatus::cases())->value,
        ];
    }
}
