<?php

namespace Database\Factories;

use App\Enums\PackageStatus;
use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vendor = fake()->unique()->slug(1);
        $package = fake()->slug(2);
        $composerName = "{$vendor}/{$package}";
        $name = Str::headline(str_replace('-', ' ', $package));

        return [
            'package_category_id' => PackageCategory::factory(),
            'name' => $name,
            'slug' => Str::slug($composerName),
            'composer_name' => $composerName,
            'description' => fake()->sentence(),
            'notes' => fake()->paragraph(),
            'source_url' => "https://github.com/{$composerName}",
            'packagist_url' => "https://packagist.org/packages/{$composerName}",
            'github_url' => "https://github.com/{$composerName}",
            'docs_url' => "https://github.com/{$composerName}#readme",
            'install_command' => "composer require {$composerName}",
            'post_install_commands' => [],
            'publish_commands' => [],
            'migration_commands' => [],
            'required_services' => [],
            'laravel_version_constraints' => ['^13.0'],
            'php_version_constraints' => ['^8.4'],
            'tags' => fake()->words(3),
            'maintainer_name' => fake()->company(),
            'status' => PackageStatus::Curated,
            'is_official' => false,
            'is_featured' => false,
            'stars' => fake()->numberBetween(0, 50000),
            'downloads' => fake()->numberBetween(0, 5000000),
            'usage_count' => fake()->numberBetween(0, 200000),
            'last_release_at' => fake()->dateTimeBetween('-1 year'),
            'last_source_update_at' => fake()->dateTimeBetween('-6 months'),
            'stats_refreshed_at' => now(),
        ];
    }
}
