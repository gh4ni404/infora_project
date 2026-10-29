<?php

namespace Database\Factories;

use App\Models\Jurusan;
use App\Models\Mapel;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mapel>
 */
class MapelFactory extends Factory
{
    protected $model = Mapel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subjects = [
            ['kode' => 'PAI', 'nama' => 'Pendidikan Agama Islam', 'singkatan' => 'PAI', 'kelompok' => 'Umum', 'beban_jam' => 3],
            ['kode' => 'PPKN', 'nama' => 'Pendidikan Pancasila', 'singkatan' => 'PPKN', 'kelompok' => 'Umum', 'beban_jam' => 2],
            ['kode' => 'BIND', 'nama' => 'Bahasa Indonesia', 'singkatan' => 'B.IND', 'kelompok' => 'Umum', 'beban_jam' => 4],
            ['kode' => 'MAT', 'nama' => 'Matematika', 'singkatan' => 'MTK', 'kelompok' => 'Umum', 'beban_jam' => 4],
            ['kode' => 'BING', 'nama' => 'Bahasa Inggris', 'singkatan' => 'B.ING', 'kelompok' => 'Umum', 'beban_jam' => 3],
            ['kode' => 'PJOK', 'nama' => 'Pendidikan Jasmani dan Rohani', 'singkatan' => 'PJOK', 'kelompok' => 'Umum', 'beban_jam' => 3],
            ['kode' => 'SEJARAH', 'nama' => 'Sejarah Indonesia', 'singkatan' => 'SEJ', 'kelompok' => 'Umum', 'beban_jam' => 2],
            ['kode' => 'SENI', 'nama' => 'Seni Budaya', 'singkatan' => 'SENI', 'kelompok' => 'Umum', 'beban_jam' => 2],
            ['kode' => 'BDER', 'nama' => 'Bahasa Daerah Bugis', 'singkatan' => 'BDER', 'kelompok' => 'Muatan Lokal', 'beban_jam' => 2],
            ['kode' => 'PROG', 'nama' => 'Pemrograman Web dan Bergerak', 'singkatan' => 'PWB', 'kelompok' => 'Kejuruan / Peminatan', 'beban_jam' => 6],
            ['kode' => 'BDATA', 'nama' => 'Basis Data Relasional', 'singkatan' => 'BD', 'kelompok' => 'Kejuruan / Peminatan', 'beban_jam' => 4],
        ];

        $subject = fake()->randomElement($subjects);

        return [
            'school_id' => School::factory(),
            'jurusan_id' => null,
            'kode' => $subject['kode'].'-'.fake()->numberBetween(10, 99),
            'nama' => $subject['nama'],
            'singkatan' => $subject['singkatan'],
            'kelompok' => $subject['kelompok'],
            'beban_jam' => $subject['beban_jam'],
            'is_active' => true,
            'keterangan' => fake()->optional()->sentence(),
        ];
    }

    /**
     * State untuk mapel Umum.
     */
    public function umum(): static
    {
        return $this->state(fn () => [
            'kelompok' => 'Umum',
            'jurusan_id' => null,
        ]);
    }

    /**
     * State untuk mapel Kejuruan / Peminatan dengan relasi jurusan.
     */
    public function kejuruan(?Jurusan $jurusan = null): static
    {
        return $this->state(fn (array $attributes) => [
            'kelompok' => 'Kejuruan / Peminatan',
            'jurusan_id' => $jurusan?->id ?? Jurusan::factory()->state(['school_id' => $attributes['school_id']]),
        ]);
    }

    /**
     * State untuk mapel Muatan Lokal.
     */
    public function muatanLokal(): static
    {
        return $this->state(fn () => [
            'kelompok' => 'Muatan Lokal',
            'jurusan_id' => null,
        ]);
    }

    /**
     * State untuk mapel Pilihan.
     */
    public function pilihan(): static
    {
        return $this->state(fn () => [
            'kelompok' => 'Pilihan',
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
