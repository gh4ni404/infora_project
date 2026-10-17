<?php

namespace Database\Factories;

use App\Models\Gtk;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gtk>
 */
class GtkFactory extends Factory
{
    /**
     * Nama model terkait.
     *
     * @var class-string<Gtk>
     */
    protected $model = Gtk::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['L', 'P']);

        return [
            'school_id' => School::factory(),
            'user_id' => null,
            'nama_lengkap' => fake()->name($gender === 'L' ? 'male' : 'female'),
            'gelar_depan' => fake()->optional(0.3)->randomElement(['Dr.', 'Drs.', 'Ir.']),
            'gelar_belakang' => fake()->optional(0.8)->randomElement(['S.Pd.', 'M.Pd.', 'S.Kom.', 'M.Kom.', 'S.T.']),
            'jenis_kelamin' => $gender,
            'nik' => fake()->numerify('730810##########'),
            'nip' => fake()->optional(0.5)->numerify('19850101201001#00#'),
            'nuptk' => fake()->optional(0.6)->numerify('################'),
            'jenis_ptk' => ['Guru Mata Pelajaran'],
            'status_kepegawaian' => fake()->randomElement(['PNS', 'PPPK', 'GTY/PTY (Tetap Yayasan)', 'Honorer Sekolah']),
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->date('Y-m-d', '-25 years'),
            'agama' => 'Islam',
            'no_hp' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'alamat' => fake()->address(),
            'pendidikan_terakhir' => 'Sarjana / Diploma IV (S1/D4)',
            'jurusan_pendidikan' => 'Pendidikan Teknik Informatika',
            'foto_path' => null,
            'is_active' => true,
        ];
    }

    /**
     * State untuk GTK Tenaga Pendidik (Guru).
     */
    public function pendidik(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_ptk' => ['Guru Mata Pelajaran', 'Wali Kelas'],
        ]);
    }

    /**
     * State untuk GTK Tenaga Kependidikan (Tendik).
     */
    public function tendik(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_ptk' => ['Tenaga Administrasi Sekolah (Staf TU)'],
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
