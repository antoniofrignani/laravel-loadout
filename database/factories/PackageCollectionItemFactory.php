<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\PackageCollection;
use App\Models\PackageCollectionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PackageCollectionItem>
 */
class PackageCollectionItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'package_collection_id' => PackageCollection::factory(),
            'package_id' => Package::factory(),
            'notes' => fake()->optional()->sentence(),
            'position' => fake()->numberBetween(0, 20),
        ];
    }
}
