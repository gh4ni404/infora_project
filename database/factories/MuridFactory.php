<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Murid;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Murid>
 */
class MuridFactory extends Factory
{
    protected $model = Murid::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jk = fake()->randomElement(['L', 'P']);
        $gender = $jk === 'L' ? 'male' : 'female';
        $currentYear = (int) date('Y');

        return [
            'school_id' => School::factory(),
            'user_id' => null,
            'kelas_id' => null,
            'angkatan' => (string) fake()->randomElement([$currentYear - 2, $currentYear - 1, $currentYear]),
            'nisn' => '00'.fake()->unique()->numerify('########'),
            'nis' => fake()->unique()->numerify('#####'),
            'nik' => fake()->numerify('7308##############'),
            'nama_lengkap' => fake()->name($gender),
            'jenis_kelamin' => $jk,
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->dateTimeBetween('-18 years', '-15 years')->format('Y-m-d'),
            'agama' => fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
            'no_hp' => '08'.fake()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'provinsi_id' => null,
            'kabupaten_id' => null,
            'kecamatan_id' => null,
            'kelurahan_id' => null,
            'alamat' => fake()->streetAddress(),
            'nama_ayah' => fake()->name('male'),
            'nama_ibu' => fake()->name('female'),
            'nama_wali' => null,
            'no_hp_ortu' => '08'.fake()->numerify('##########'),
            'foto_path' => null,
            'status' => 'aktif',
            'is_active' => true,
        ];
    }

    /**
     * State untuk murid non-aktif.
     */
    public function nonaktif(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    /**
     * State dengan status lulus.
     */
    public function lulus(): static
    {
        return $this->state(fn () => [
            'status' => 'lulus',
            'is_active' => false,
        ]);
    }

    /**
     * State dengan kelas tertentu.
     */
    public function denganKelas(?Kelas $kelas = null): static
    {
        return $this->state(fn (array $attributes) => [
            'kelas_id' => $kelas?->id ?? Kelas::factory()->state(['school_id' => $attributes['school_id']]),
        ]);
    }

    /**
     * State dengan akun pengguna User login.
     */
    public function denganUser(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user?->id ?? User::factory()->state([
                'school_id' => $attributes['school_id'],
                'user_type' => 'murid',
            ]),
        ]);
    }
}
