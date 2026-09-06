<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $schoolType = fake()->randomElement(['SMA', 'SMK']);
        $status = fake()->randomElement(['Negeri', 'Swasta']);

        return [
            'name' => $schoolType.' '.$status.' '.fake()->numberBetween(1, 20).' '.fake()->city(),
            'npsn' => (string) fake()->unique()->numberBetween(10000000, 99999999),
            'nss' => (string) fake()->optional()->numberBetween(10000000000, 99999999999),
            'school_type' => $schoolType,
            'status' => $status,
            'accreditation' => fake()->randomElement(['A', 'B', 'C', 'Belum', null]),
            'address' => fake()->streetAddress(),
            'village' => fake()->optional()->citySuffix(),
            'district' => fake()->optional()->city(),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'website' => fake()->optional()->url(),
            'is_active' => true,
        ];
    }

    /**
     * State: SMA school.
     */
    public function sma(): static
    {
        return $this->state(fn (array $attributes) => [
            'school_type' => 'SMA',
            'name' => 'SMA '.$attributes['status'].' '.fake()->numberBetween(1, 20).' '.fake()->city(),
        ]);
    }

    /**
     * State: SMK school.
     */
    public function smk(): static
    {
        return $this->state(fn (array $attributes) => [
            'school_type' => 'SMK',
            'name' => 'SMK '.$attributes['status'].' '.fake()->numberBetween(1, 20).' '.fake()->city(),
        ]);
    }

    /**
     * State: Public (Negeri) school.
     */
    public function negeri(): static
    {
        return $this->state(fn () => [
            'status' => 'Negeri',
        ]);
    }

    /**
     * State: Private (Swasta) school.
     */
    public function swasta(): static
    {
        return $this->state(fn () => [
            'status' => 'Swasta',
            'foundation_name' => 'Yayasan '.fake()->company(),
        ]);
    }
}
