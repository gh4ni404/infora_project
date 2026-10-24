<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Tingkatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tingkatan>
 */
class TingkatanFactory extends Factory
{
    protected $model = Tingkatan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tingkatAngka = fake()->numberBetween(1, 13);

        return [
            'school_id' => School::factory(),
            'kode' => 'T-'.fake()->unique()->numberBetween(1, 9999),
            'nama' => 'Tingkat '.$tingkatAngka,
            'tingkat_angka' => $tingkatAngka,
            'fase' => fake()->randomElement(['Fase E', 'Fase F', null]),
            'is_active' => true,
            'keterangan' => fake()->optional()->sentence(),
        ];
    }

    /**
     * State untuk Tingkat 10 / Kelas X (Fase E).
     */
    public function kelas10(): static
    {
        return $this->state(fn () => [
            'kode' => 'X',
            'nama' => 'Kelas 10',
            'tingkat_angka' => 10,
            'fase' => 'Fase E',
            'is_active' => true,
        ]);
    }

    /**
     * State untuk Tingkat 11 / Kelas XI (Fase F).
     */
    public function kelas11(): static
    {
        return $this->state(fn () => [
            'kode' => 'XI',
            'nama' => 'Kelas 11',
            'tingkat_angka' => 11,
            'fase' => 'Fase F',
            'is_active' => true,
        ]);
    }

    /**
     * State untuk Tingkat 12 / Kelas XII (Fase F).
     */
    public function kelas12(): static
    {
        return $this->state(fn () => [
            'kode' => 'XII',
            'nama' => 'Kelas 12',
            'tingkat_angka' => 12,
            'fase' => 'Fase F',
            'is_active' => true,
        ]);
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
