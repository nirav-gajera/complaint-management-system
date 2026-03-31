<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\ComplaintCategory>
 */
class ComplaintCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Service', 'Product', 'Feedback', 'Other']).' '.fake()->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => fake()->randomElement(['service', 'product', 'feedback', 'other']),
            'description' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 99),
        ];
    }
}
