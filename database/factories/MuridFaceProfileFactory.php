<?php

namespace Database\Factories;

use App\Models\Murid;
use App\Models\MuridFaceProfile;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MuridFaceProfile>
 */
class MuridFaceProfileFactory extends Factory
{
    protected $model = MuridFaceProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $descriptor = [];
        for ($i = 0; $i < 128; $i++) {
            $descriptor[] = round(fake()->randomFloat(6, -0.35, 0.35), 6);
        }

        return [
            'school_id' => School::factory(),
            'murid_id' => fn (array $attributes) => Murid::factory()->state([
                'school_id' => $attributes['school_id'],
            ]),
            'label' => fake()->randomElement(['Tampak Depan', 'Agak Kiri', 'Agak Kanan', 'Berkacamata']),
            'foto_path' => 'faces/murid/face_murid_u1_20261125_080000_'.fake()->lexify('????????').'.jpg',
            'face_descriptor' => $descriptor,
            'confidence' => fake()->randomFloat(4, 0.78, 0.99),
            'model_version' => MuridFaceProfile::DEFAULT_MODEL_VERSION,
            'is_active' => true,
            'registered_by' => User::factory(),
        ];
    }

    /**
     * State untuk sampel wajah yang dinonaktifkan.
     */
    public function nonaktif(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
