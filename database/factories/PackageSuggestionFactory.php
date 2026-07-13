<?php

namespace Database\Factories;

use App\Enums\PackageSuggestionStatus;
use App\Models\Loadout;
use App\Models\PackageSuggestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PackageSuggestion>
 */
class PackageSuggestionFactory extends Factory
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
            'user_id' => User::factory(),
            'loadout_id' => Loadout::factory(),
            'input' => "https://packagist.org/packages/{$composerName}",
            'display_name' => fake()->words(2, true),
            'composer_name' => $composerName,
            'source_url' => "https://packagist.org/packages/{$composerName}",
            'packagist_url' => "https://packagist.org/packages/{$composerName}",
            'status' => PackageSuggestionStatus::Pending,
            'lookup_payload' => ['resolved' => true],
            'moderated_at' => null,
            'moderated_by' => null,
        ];
    }
}
