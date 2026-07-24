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

    public function test_first_party_foundation_choices_shape_generated_artifacts(): void
    {
        $this->withoutVite();

        $this->get(route('builder.starter'))->assertOk();
        $this->put(route('builder.starter.update'), [
            'frontend_stack' => 'react-inertia',
            'authentication' => 'laravel-auth',
            'teams' => false,
            'dark_mode' => 'system',
            'api_auth' => 'sanctum',
            'broadcasting' => 'reverb',
            'search' => 'scout',
            'queue_monitoring' => 'horizon',
            'observability' => 'pulse',
        ])->assertRedirect(route('builder.runtime'));

        $this->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('artifacts.0.content', fn (string $content): bool => str_contains($content, '"laravel/reverb": "*"')
                    && str_contains($content, '"laravel/sanctum": "*"')
                    && str_contains($content, '"laravel/scout": "*"')
                    && str_contains($content, '"laravel/horizon": "*"')
                    && str_contains($content, '"laravel/pulse": "*"')
                    && str_contains($content, '"reverb": "@php artisan reverb:start"'))
                ->where('artifacts.1.content', fn (string $content): bool => str_contains($content, '"laravel-echo": "^2.0.0"')
                    && str_contains($content, '"pusher-js": "^8.4.0"'))
                ->where('artifacts.2.content', fn (string $content): bool => str_contains($content, 'FORWARD_REVERB_PORT')
                    && str_contains($content, 'redis:'))
                ->where('artifacts.3.content', fn (string $content): bool => str_contains($content, 'BROADCAST_CONNECTION=reverb')
                    && str_contains($content, 'QUEUE_CONNECTION=redis'))
                ->where('artifacts.4.content', fn (string $content): bool => str_contains($content, 'reverb:install')
                    && str_contains($content, 'horizon:install')
                    && str_contains($content, 'scout:install')
                    && str_contains($content, 'pulse:install'))
                ->has('artifacts', 6)
                ->where('artifacts.5.name', 'resources/js/echo.ts'),
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

    public function test_generated_artifacts_use_the_selected_package_manager(): void
    {
        $this->withoutVite();

        $this->put(route('builder.project.update'), [
            'project_name' => 'Bun Starter',
            'app_name' => 'Bun App',
            'php_version' => '8.5',
            'laravel_version' => '13.x',
            'package_manager' => 'bun',
        ])->assertRedirect(route('builder.starter'));

        $this->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('artifacts.1.content', fn (string $content): bool => str_contains($content, '"packageManager": "bun@1.2.0"'))
                ->where('artifacts.2.content', fn (string $content): bool => str_contains($content, 'runtimes/8.5'))
                ->where('artifacts.4.content', fn (string $content): bool => str_contains($content, '- Package manager: Bun'))
                ->where('artifacts.4.content', fn (string $content): bool => str_contains($content, './vendor/bin/sail bun install'))
                ->where('artifacts.4.content', fn (string $content): bool => str_contains($content, 'laravelsail/php85-composer:latest')),
            );
    }
}
