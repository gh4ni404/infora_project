<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TahunAjaran>
 */
class TahunAjaranFactory extends Factory
{
    protected $model = TahunAjaran::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->unique()->numberBetween(2020, 2090);
        $endYear = $startYear + 1;

        return [
            'school_id' => School::factory(),
            'tahun' => "{$startYear}/{$endYear}",
            'is_active' => true,
            'keterangan' => fake()->optional()->sentence(),
        ];
    }

    /**
     * State nonaktif.
     */
    public function nonaktif(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
