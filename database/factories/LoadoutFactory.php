<?php

namespace Database\Factories;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Loadout>
 */
class LoadoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->word().' '.fake()->word().' '.fake()->word();

        return [
            'user_id' => User::factory(),
            'public_id' => (string) Str::ulid(),
            'title' => Str::headline($title),
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'description' => fake()->sentence(),
            'visibility' => LoadoutVisibility::Private,
            'current_step' => fake()->numberBetween(1, 6),
            'builder_state' => [
                'project' => [
                    'laravel_version' => '13.x',
                    'php_version' => '8.5',
                ],
            ],
            'generation_snapshot' => null,
            'star_count' => 0,
            'clone_count' => 0,
            'published_at' => null,
            'last_generated_at' => null,
        ];
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => LoadoutVisibility::Public,
            'published_at' => now(),
        ]);
    }
}
