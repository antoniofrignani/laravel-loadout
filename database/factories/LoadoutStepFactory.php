<?php

namespace Database\Factories;

use App\Models\Loadout;
use App\Models\LoadoutStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoadoutStep>
 */
class LoadoutStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $position = fake()->numberBetween(1, 6);

        return [
            'loadout_id' => Loadout::factory(),
            'step_key' => "step_{$position}",
            'position' => $position,
            'data' => ['completed' => true],
            'completed_at' => now(),
        ];
    }
}
