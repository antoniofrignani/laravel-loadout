<?php

namespace Tests\Feature;

use App\Models\Loadout;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BuilderStarterRuntimeTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_starter_kit_page_displays_defaults(): void
    {
        $this->withoutVite();

        $this->get(route('builder.starter'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('builder/starter-kit')
                ->where('values.frontend_stack', 'react-inertia')
                ->where('values.authentication', 'laravel-auth')
                ->where('values.teams', false)
                ->where('values.dark_mode', 'system'),
            );
    }

    public function test_starter_kit_can_be_saved_to_current_draft(): void
    {
        $this->withoutVite();

        $this->get(route('builder.starter'))->assertOk();

        $this
            ->from(route('builder.starter'))
            ->put(route('builder.starter.update'), [
                'frontend_stack' => 'vue-inertia',
                'authentication' => 'none',
                'teams' => '1',
                'dark_mode' => 'enabled',
            ])
            ->assertRedirect(route('builder.runtime'));

        $loadout = Loadout::query()->firstOrFail();

        $this->assertSame('vue-inertia', data_get($loadout->builder_state, 'starter.frontend_stack'));
        $this->assertSame('none', data_get($loadout->builder_state, 'starter.authentication'));
        $this->assertTrue(data_get($loadout->builder_state, 'starter.teams'));
        $this->assertSame(3, $loadout->current_step);
    }

    public function test_runtime_page_displays_defaults(): void
    {
        $this->withoutVite();

        $this->get(route('builder.runtime'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('builder/runtime')
                ->where('values.database', 'mysql')
                ->where('values.cache', 'database')
                ->where('values.queue', 'database')
                ->where('values.mail', 'mailpit')
                ->where('values.search', 'none')
                ->where('values.local_runtime', 'sail'),
            );
    }

    public function test_runtime_can_be_saved_and_feeds_generated_artifacts(): void
    {
        $this->withoutVite();

        $this->get(route('builder.runtime'))->assertOk();

        $this
            ->from(route('builder.runtime'))
            ->put(route('builder.runtime.update'), [
                'database' => 'pgsql',
                'cache' => 'redis',
                'queue' => 'redis',
                'mail' => 'mailpit',
                'search' => 'meilisearch',
                'local_runtime' => 'sail',
            ])
            ->assertRedirect(route('builder.packages'));

        $loadout = Loadout::query()->firstOrFail();

        $this->assertSame('pgsql', data_get($loadout->builder_state, 'runtime.database'));
        $this->assertSame('redis', data_get($loadout->builder_state, 'runtime.cache'));
        $this->assertSame(4, $loadout->current_step);

        $this->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('artifacts.2.content', fn (string $content): bool => str_contains($content, 'pgsql:') && str_contains($content, 'redis:') && str_contains($content, 'meilisearch:'))
                ->where('artifacts.3.content', fn (string $content): bool => str_contains($content, 'DB_CONNECTION=pgsql') && str_contains($content, 'CACHE_STORE=redis') && str_contains($content, 'SCOUT_DRIVER=meilisearch'))
                ->where('artifacts.4.content', fn (string $content): bool => str_contains($content, '- Database: Pgsql') && str_contains($content, '- Cache: Redis')),
            );
    }

    public function test_starter_and_runtime_validation_rejects_invalid_choices(): void
    {
        $this
            ->from(route('builder.starter'))
            ->put(route('builder.starter.update'), [
                'frontend_stack' => 'jquery',
                'authentication' => 'custom',
                'teams' => 'yes-please',
                'dark_mode' => 'maybe',
            ])
            ->assertRedirect(route('builder.starter'))
            ->assertSessionHasErrors(['frontend_stack', 'authentication', 'teams', 'dark_mode']);

        $this
            ->from(route('builder.runtime'))
            ->put(route('builder.runtime.update'), [
                'database' => 'oracle',
                'cache' => 'memcached',
                'queue' => 'sqs',
                'mail' => 'postmark',
                'search' => 'algolia',
                'local_runtime' => 'docker',
            ])
            ->assertRedirect(route('builder.runtime'))
            ->assertSessionHasErrors(['database', 'cache', 'queue', 'mail', 'search', 'local_runtime']);
    }
}
