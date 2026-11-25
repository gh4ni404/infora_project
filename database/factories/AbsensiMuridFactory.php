<?php

namespace Database\Factories;

use App\Models\AbsensiMurid;
use App\Models\Murid;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AbsensiMurid>
 */
class AbsensiMuridFactory extends Factory
{
    protected $model = AbsensiMurid::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'murid_id' => fn (array $attributes) => Murid::factory()->state([
                'school_id' => $attributes['school_id'],
            ]),
            'kelas_id' => null,
            'tahun_ajaran_id' => null,
            'semester_id' => null,
            'face_profile_id' => null,
            'tanggal' => today()->toDateString(),
            'jam_masuk' => now(),
            'jam_pulang' => null,
            'status' => 'hadir',
            'metode' => 'face_recognition',
            'match_score' => 0.865,
            'foto_masuk_path' => null,
            'foto_pulang_path' => null,
            'keterangan' => null,
            'recorded_by' => User::factory(),
        ];
    }
}
