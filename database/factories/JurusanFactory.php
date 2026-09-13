<?php

namespace Database\Factories;

use App\Models\Jurusan;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jurusan>
 */
class JurusanFactory extends Factory
{
    /**
     * Nama model terkait.
     *
     * @var class-string<Jurusan>
     */
    protected $model = Jurusan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jenjang = fake()->randomElement(['SMK', 'SMA']);
        $kode = strtoupper(fake()->unique()->lexify('???'));

        return [
            'school_id' => School::factory(),
            'kode' => $kode,
            'nama' => 'Jurusan '.$kode.' '.fake()->words(2, true),
            'singkatan' => $kode,
            'jenjang' => $jenjang,
            'bidang_keahlian' => $jenjang === 'SMK' ? 'Teknologi Informasi' : null,
            'program_keahlian' => $jenjang === 'SMK' ? 'Pengembangan Perangkat Lunak' : null,
            'kepala_jurusan' => fake()->name(),
            'is_active' => true,
            'deskripsi' => fake()->optional()->sentence(),
        ];
    }

    /**
     * State untuk jenjang SMK.
     */
    public function smk(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenjang' => 'SMK',
            'kode' => 'RPL',
            'nama' => 'Rekayasa Perangkat Lunak',
            'singkatan' => 'RPL',
            'bidang_keahlian' => 'Teknologi Informasi',
            'program_keahlian' => 'Pengembangan Perangkat Lunak dan Gim',
        ]);
    }

    /**
     * State untuk jenjang SMA.
     */
    public function sma(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenjang' => 'SMA',
            'kode' => 'MIPA',
            'nama' => 'Matematika dan Ilmu Pengetahuan Alam',
            'singkatan' => 'MIPA',
            'bidang_keahlian' => null,
            'program_keahlian' => null,
        ]);
    }

    /**
     * State berstatus aktif.
     */
    public function aktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * State berstatus nonaktif.
     */
    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
