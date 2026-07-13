<?php

namespace Tests\Feature;

use App\Models\Loadout;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BuilderProjectBasicsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_project_basics_page_displays_current_draft_defaults(): void
    {
        $this->withoutVite();

        $this->get(route('builder.project'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('builder/project-basics')
                ->where('values.project_name', 'Untitled Loadout')
                ->where('values.app_name', 'Untitled Loadout')
                ->where('values.php_version', '8.4')
                ->where('values.laravel_version', '13.x')
                ->where('values.package_manager', 'yarn'),
            );
    }

    public function test_project_basics_can_be_saved_to_current_draft(): void
    {
        $this->withoutVite();

        $this->get(route('builder.project'))->assertOk();

        $this
            ->from(route('builder.project'))
            ->put(route('builder.project.update'), [
                'project_name' => 'Agency Starter',
                'app_name' => 'Client Portal',
                'description' => 'Reusable Laravel starter for client portals.',
                'php_version' => '8.5',
                'laravel_version' => '13.x',
                'package_manager' => 'yarn',
            ])
            ->assertRedirect(route('builder.starter'));

        $loadout = Loadout::query()->firstOrFail();

        $this->assertSame('Agency Starter', $loadout->title);
        $this->assertSame('Reusable Laravel starter for client portals.', $loadout->description);
        $this->assertSame('Client Portal', data_get($loadout->builder_state, 'project.app_name'));
        $this->assertSame('8.5', data_get($loadout->builder_state, 'project.php_version'));
        $this->assertSame(2, $loadout->current_step);

        $this->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('artifacts.0.content', fn (string $content): bool => str_contains($content, '"php": "8.5"'))
                ->where('artifacts.3.content', fn (string $content): bool => str_contains($content, 'APP_NAME="Client Portal"'))
                ->where('artifacts.4.content', fn (string $content): bool => str_contains($content, '# Agency Starter')),
            );
    }

    public function test_project_basics_validation_rejects_invalid_versions(): void
    {
        $this
            ->from(route('builder.project'))
            ->put(route('builder.project.update'), [
                'project_name' => '',
                'app_name' => 'Client Portal',
                'php_version' => '7.4',
                'laravel_version' => '12.x',
                'package_manager' => 'npm',
            ])
            ->assertRedirect(route('builder.project'))
            ->assertSessionHasErrors(['project_name', 'php_version', 'laravel_version', 'package_manager']);
    }
}
