<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BuilderReviewPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_builder_review_page_displays_generated_artifact_previews(): void
    {
        $this->withoutVite();

        $package = Package::factory()->create([
            'name' => 'Laravel Permission',
            'composer_name' => 'spatie/laravel-permission',
            'required_services' => ['redis'],
        ]);

        $this->get(route('builder.packages'))->assertOk();
        $this->post(route('builder.packages.store'), [
            'package_id' => $package->id,
        ])->assertRedirect();

        $this->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('builder/review')
                ->where('loadout.title', 'Untitled Loadout')
                ->has('artifacts', 5)
                ->where('artifacts.0.name', 'composer.json')
                ->where('artifacts.0.content', fn (string $content): bool => str_contains($content, 'spatie/laravel-permission'))
                ->where('artifacts.2.name', 'compose.yaml')
                ->where('artifacts.2.content', fn (string $content): bool => str_contains($content, 'redis:'))
                ->where('artifacts.3.name', '.env.loadout')
                ->where('artifacts.4.name', 'README.md'),
            );
    }
}
