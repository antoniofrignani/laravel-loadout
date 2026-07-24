<?php

namespace Tests\Feature;

use App\Models\Loadout;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_start_new_loadout_replaces_the_active_draft_with_a_fresh_one(): void
    {
        $this->get(route('builder.project'))->assertOk();

        $existingLoadout = Loadout::query()->sole();
        $existingLoadout->update([
            'title' => 'Existing Loadout',
            'description' => 'Keep this saved loadout unchanged.',
            'builder_state' => [
                'project' => [
                    'project_name' => 'Existing Loadout',
                    'app_name' => 'Existing App',
                ],
            ],
        ]);

        $this->post(route('builder.new'))
            ->assertRedirect(route('builder.project'));

        $this->assertSame(2, Loadout::query()->count());
        $this->assertSame('Existing Loadout', $existingLoadout->refresh()->title);

        $this->get(route('builder.project'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('values.project_name', 'Untitled Loadout')
                ->where('values.app_name', 'Untitled Loadout')
                ->where('values.description', 'Draft Laravel starter stack.'),
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
        $this->assertSame('yarn', data_get($loadout->builder_state, 'project.package_manager'));
        $this->assertSame(2, $loadout->current_step);

        $this->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('artifacts.0.content', fn (string $content): bool => str_contains($content, '"php": "8.5"'))
                ->where('artifacts.3.content', fn (string $content): bool => str_contains($content, 'APP_NAME="Client Portal"'))
                ->where('artifacts.4.content', fn (string $content): bool => str_contains($content, '# Agency Starter')),
            );
    }

    #[DataProvider('packageManagers')]
    public function test_project_basics_accepts_laravel_installer_package_managers(string $packageManager): void
    {
        $this
            ->put(route('builder.project.update'), [
                'project_name' => 'Package Manager Test',
                'app_name' => 'Client Portal',
                'php_version' => '8.5',
                'laravel_version' => '13.x',
                'package_manager' => $packageManager,
            ])
            ->assertRedirect(route('builder.starter'));

        $loadout = Loadout::query()->firstOrFail();

        $this->assertSame($packageManager, data_get($loadout->builder_state, 'project.package_manager'));
    }

    public function test_project_basics_validation_rejects_invalid_versions_and_package_manager(): void
    {
        $this
            ->from(route('builder.project'))
            ->put(route('builder.project.update'), [
                'project_name' => '',
                'app_name' => 'Client Portal',
                'php_version' => '7.4',
                'laravel_version' => '12.x',
                'package_manager' => 'composer',
            ])
            ->assertRedirect(route('builder.project'))
            ->assertSessionHasErrors(['project_name', 'php_version', 'laravel_version', 'package_manager']);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function packageManagers(): array
    {
        return [
            'npm' => ['npm'],
            'pnpm' => ['pnpm'],
            'bun' => ['bun'],
            'yarn' => ['yarn'],
        ];
    }
}
