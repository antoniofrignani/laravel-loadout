<?php

namespace Tests\Feature;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
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

    public function test_sail_readme_setup_runs_dependency_installs_inside_sail(): void
    {
        $this->withoutVite();

        $this->get(route('builder.runtime'))->assertOk();
        $this->put(route('builder.runtime.update'), [
            'database' => 'mysql',
            'cache' => 'database',
            'queue' => 'database',
            'mail' => 'mailpit',
            'search' => 'none',
            'local_runtime' => 'sail',
        ])->assertRedirect(route('builder.packages'));

        $this->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('artifacts.0.content', fn (string $content): bool => str_contains($content, '"laravel/sail": "^1.48"'))
                ->where('artifacts.4.content', function (string $content): bool {
                    return str_contains($content, 'laravelsail/php84-composer:latest')
                    && str_contains($content, './vendor/bin/sail yarn install')
                    && str_contains($content, './vendor/bin/sail artisan migrate')
                    && ! str_contains($content, './vendor/bin/sail composer install')
                    && ! str_contains($content, "\ncomposer install\n")
                    && ! str_contains($content, "\nyarn install\n");
                }),
            );
    }

    public function test_readme_includes_the_public_url_only_after_the_loadout_is_published(): void
    {
        $this->withoutVite();

        $this->get(route('builder.packages'))->assertOk();

        $this->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('artifacts.4.content', fn (string $content): bool => str_contains(
                    $content,
                    'Shareable template URL: Publish this loadout in Laravel Loadout to generate a public URL.',
                )),
            );

        $loadout = Loadout::query()->firstOrFail();
        $loadout->update([
            'visibility' => LoadoutVisibility::Unlisted,
            'published_at' => now(),
        ]);

        $this->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('artifacts.4.content', fn (string $content): bool => str_contains(
                    $content,
                    'Shareable template URL: '.route('loadouts.show', $loadout),
                )),
            );
    }

    public function test_non_sail_readme_setup_uses_host_commands(): void
    {
        $this->withoutVite();

        $this->get(route('builder.runtime'))->assertOk();
        $this->put(route('builder.runtime.update'), [
            'database' => 'sqlite',
            'cache' => 'database',
            'queue' => 'database',
            'mail' => 'log',
            'search' => 'none',
            'local_runtime' => 'herd',
        ])->assertRedirect(route('builder.packages'));

        $this->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('artifacts.0.content', fn (string $content): bool => ! str_contains($content, '"laravel/sail"'))
                ->where('artifacts.4.content', function (string $content): bool {
                    return str_contains($content, "\ncomposer install\n")
                        && str_contains($content, "\nyarn install\n")
                        && str_contains($content, "\nphp artisan migrate\n")
                        && ! str_contains($content, './vendor/bin/sail composer install');
                }),
            );
    }
}
