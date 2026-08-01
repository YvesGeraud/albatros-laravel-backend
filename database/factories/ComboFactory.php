<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Combo>
 */
class ComboFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1, 100000),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 1000, 15000),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
