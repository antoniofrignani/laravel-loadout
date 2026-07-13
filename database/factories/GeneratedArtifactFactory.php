<?php

namespace Database\Factories;

use App\Models\GeneratedArtifact;
use App\Models\Loadout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GeneratedArtifact>
 */
class GeneratedArtifactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $content = fake()->paragraph();

        return [
            'loadout_id' => Loadout::factory(),
            'filename' => fake()->randomElement(['composer.json', 'package.json', 'README.md', '.env.loadout']),
            'mime_type' => 'text/plain',
            'content_hash' => hash('sha256', $content),
            'content' => $content,
            'metadata' => [],
        ];
    }
}
