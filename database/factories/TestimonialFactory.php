<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Testimonial>
 */
class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_name' => $this->faker->name(),
            'event_type' => $this->faker->randomElement(['Boda', 'XV años', 'Fiesta empresarial', 'Cumpleaños']),
            'content' => $this->faker->paragraph(),
            'rating' => $this->faker->numberBetween(4, 5),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
