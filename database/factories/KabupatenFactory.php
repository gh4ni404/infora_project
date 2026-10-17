<?php

namespace Database\Factories;

use App\Models\Kabupaten;
use App\Models\Provinsi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kabupaten>
 */
class KabupatenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provinsi = Provinsi::inRandomOrder()->first() ?? Provinsi::factory()->create();
        $kode = (string) fake()->unique()->numberBetween(1000, 9999);

        return [
            'provinsi_id' => $provinsi->id,
            'kode' => $kode,
            'tipe' => fake()->randomElement(['Kabupaten', 'Kota']),
            'nama' => fake()->city(),
            'status' => true,
        ];
    }

    /**
     * State untuk kabupaten nonaktif.
     */
    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
