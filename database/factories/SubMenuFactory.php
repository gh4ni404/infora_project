<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\SubMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubMenu>
 */
class SubMenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'name' => fake()->words(2, true),
            'route_name' => fake()->slug(),
            'order' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }
}
