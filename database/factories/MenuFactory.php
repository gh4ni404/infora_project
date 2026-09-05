<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'name' => fake()->words(2, true),
            'route_name' => fake()->slug(),
            'icon' => 'menu',
            'order' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }
}
