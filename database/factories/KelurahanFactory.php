<?php

namespace Database\Factories;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kelurahan>
 */
class KelurahanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $kecamatan = Kecamatan::inRandomOrder()->first() ?? Kecamatan::factory()->create();
        $subKode = str_pad((string) fake()->unique()->numberBetween(1001, 9999), 4, '0', STR_PAD_LEFT);
        $kode = $kecamatan->kode.$subKode;

        return [
            'kecamatan_id' => $kecamatan->id,
            'kode' => $kode,
            'tipe' => fake()->randomElement(['Kelurahan', 'Desa']),
            'nama' => fake()->streetName(),
            'kode_pos' => (string) fake()->numberBetween(90000, 99999),
            'status' => true,
        ];
    }

    /**
     * State untuk entitas bertipe Kelurahan.
     */
    public function kelurahan(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipe' => 'Kelurahan',
        ]);
    }

    /**
     * State untuk entitas bertipe Desa.
     */
    public function desa(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipe' => 'Desa',
        ]);
    }

    /**
     * State untuk kelurahan/desa nonaktif.
     */
    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
