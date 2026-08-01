<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 100000),
            'description' => $this->faker->paragraph(),
            'venue_name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(19, 20),
            'longitude' => $this->faker->longitude(-98, -97),
            'event_date' => now()->addDays(10),
            'is_live' => false,
        ];
    }

    public function past(): static
    {
        return $this->state(fn () => ['event_date' => now()->subDays(10)]);
    }

    public function live(): static
    {
        return $this->state(fn () => ['is_live' => true, 'event_date' => now()]);
    }
}
