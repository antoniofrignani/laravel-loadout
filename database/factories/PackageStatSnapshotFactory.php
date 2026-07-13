<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\PackageStatSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PackageStatSnapshot>
 */
class PackageStatSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'package_id' => Package::factory(),
            'source' => fake()->randomElement(['packagist', 'github']),
            'stars' => fake()->numberBetween(0, 50000),
            'downloads' => fake()->numberBetween(0, 5000000),
            'usage_count' => fake()->numberBetween(0, 200000),
            'last_release_at' => fake()->dateTimeBetween('-1 year'),
            'last_source_update_at' => fake()->dateTimeBetween('-6 months'),
            'refreshed_at' => now(),
            'error_message' => null,
            'metadata' => ['source_id' => fake()->uuid()],
        ];
    }
}
