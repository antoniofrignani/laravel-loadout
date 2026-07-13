<?php

namespace Database\Factories;

use App\Models\Loadout;
use App\Models\LoadoutPackage;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoadoutPackage>
 */
class LoadoutPackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $composerName = fake()->unique()->slug(1).'/'.fake()->slug(2);

        return [
            'loadout_id' => Loadout::factory(),
            'package_id' => Package::factory(),
            'display_name' => fake()->words(2, true),
            'composer_name' => $composerName,
            'source_url' => "https://packagist.org/packages/{$composerName}",
            'is_custom' => false,
            'package_snapshot' => [
                'composer_name' => $composerName,
                'install_command' => "composer require {$composerName}",
            ],
            'install_options' => [],
            'notes' => null,
            'position' => fake()->numberBetween(0, 20),
        ];
    }

    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'package_id' => null,
            'is_custom' => true,
        ]);
    }
}
