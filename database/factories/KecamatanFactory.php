<?php

namespace Database\Factories;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kecamatan>
 */
class KecamatanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $kabupaten = Kabupaten::inRandomOrder()->first() ?? Kabupaten::factory()->create();
        $subKode = str_pad((string) fake()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT);
        $kode = $kabupaten->kode.$subKode;

        return [
            'kabupaten_id' => $kabupaten->id,
            'kode' => $kode,
            'nama' => fake()->city(),
            'status' => true,
        ];
    }

    /**
     * State untuk kecamatan nonaktif.
     */
    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
