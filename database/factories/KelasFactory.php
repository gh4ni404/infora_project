<?php

namespace Database\Factories;

use App\Models\Gtk;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\School;
use App\Models\TahunAjaran;
use App\Models\Tingkatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kelas>
 */
class KelasFactory extends Factory
{
    protected $model = Kelas::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tingkat = fake()->randomElement(['X', 'XI', 'XII']);
        $jurusanCode = fake()->randomElement(['PPLG', 'TKJ', 'DKV', 'AKL', 'MPLB']);
        $paralel = fake()->numberBetween(1, 5);

        return [
            'school_id' => School::factory(),
            'tahun_ajaran_id' => TahunAjaran::factory(),
            'tingkatan_id' => Tingkatan::factory(),
            'jurusan_id' => null,
            'wali_kelas_id' => null,
            'nama' => "{$tingkat} {$jurusanCode} {$paralel} - ".fake()->unique()->numberBetween(100, 999),
            'kode' => "{$tingkat}-{$jurusanCode}-{$paralel}",
            'ruangan' => 'Ruang '.fake()->numberBetween(101, 310),
            'kapasitas' => 36,
            'is_active' => true,
            'keterangan' => fake()->optional()->sentence(),
        ];
    }

    /**
     * State untuk kelas non-aktif.
     */
    public function nonaktif(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    /**
     * State dengan jurusan tertentu.
     */
    public function denganJurusan(?Jurusan $jurusan = null): static
    {
        return $this->state(fn (array $attributes) => [
            'jurusan_id' => $jurusan?->id ?? Jurusan::factory()->state(['school_id' => $attributes['school_id']]),
        ]);
    }

    /**
     * State dengan wali kelas tertentu.
     */
    public function denganWaliKelas(?Gtk $gtk = null): static
    {
        return $this->state(fn (array $attributes) => [
            'wali_kelas_id' => $gtk?->id ?? Gtk::factory()->state(['school_id' => $attributes['school_id']]),
        ]);
    }
}
