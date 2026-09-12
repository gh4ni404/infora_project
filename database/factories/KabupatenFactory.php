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
        $subKode = str_pad((string) fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT);
        $kode = $provinsi->kode.$subKode;

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
