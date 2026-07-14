<?php

namespace Tests\Feature;

use App\Mail\PackageSuggestionSubmitted;
use App\Models\Loadout;
use App\Models\LoadoutPackage;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageSuggestion;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BuilderPackageCatalogPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_builder_package_catalog_page_is_displayed(): void
    {
        $this->withoutVite();

        $category = PackageCategory::factory()->create([
            'name' => 'Developer experience',
            'slug' => 'developer-experience',
        ]);
        Package::factory()->for($category, 'category')->create([
            'name' => 'Laravel Debugbar',
            'composer_name' => 'barryvdh/laravel-debugbar',
            'description' => 'Local request inspection toolbar',
            'stars' => 12000,
        ]);

        $this->get(route('builder.packages'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('builder/packages')
                ->has('categories', 1)
                ->where('categories.0.slug', 'developer-experience')
                ->where('categories.0.packages_count', 1)
                ->where('loadout.title', 'Untitled Loadout')
                ->where('loadout.selected_package_ids', [])
                ->has('packages.data', 1)
                ->where('packages.data.0.composer_name', 'barryvdh/laravel-debugbar')
                ->where('packages.data.0.category.slug', 'developer-experience'),
            );
    }

    public function test_builder_package_catalog_page_respects_filters(): void
    {
        $this->withoutVite();

        $developerExperience = PackageCategory::factory()->create([
            'slug' => 'developer-experience',
        ]);
        $media = PackageCategory::factory()->create([
            'slug' => 'media-and-uploads',
        ]);

        Package::factory()->for($developerExperience, 'category')->create([
            'name' => 'Laravel Debugbar',
            'composer_name' => 'barryvdh/laravel-debugbar',
            'description' => 'Local request inspection toolbar',
            'required_services' => [],
        ]);
        Package::factory()->for($media, 'category')->create([
            'name' => 'Laravel Medialibrary',
            'composer_name' => 'spatie/laravel-medialibrary',
            'description' => 'File attachments',
            'required_services' => [],
        ]);

        $this->get(route('builder.packages', [
            'search' => 'media',
            'category' => 'media-and-uploads',
        ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('builder/packages')
                ->where('filters.search', 'media')
                ->where('filters.category', 'media-and-uploads')
                ->has('packages.data', 1)
                ->where('packages.data.0.composer_name', 'spatie/laravel-medialibrary'),
            );
    }

    public function test_builder_package_catalog_page_respects_per_page(): void
    {
        $this->withoutVite();

        Package::factory()->count(5)->create();

        $this->get(route('builder.packages', [
            'per_page' => 2,
        ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('builder/packages')
                ->where('filters.per_page', 2)
                ->has('packages.data', 2)
                ->where('packages.total', 5),
            );
    }

    public function test_builder_package_catalog_page_normalizes_nullable_package_metadata(): void
    {
        $this->withoutVite();

        Package::factory()->create([
            'name' => 'Minimal Package',
            'composer_name' => 'vendor/minimal-package',
            'tags' => null,
            'required_services' => null,
        ]);

        $this->get(route('builder.packages'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('builder/packages')
                ->where('packages.data.0.tags', [])
                ->where('packages.data.0.required_services', [])
                ->where('packages.data.0.compatibility_warnings', [])
                ->where('loadout.selected_package_ids', [])
                ->where('loadout.selected_packages', [])
                ->where('loadout.custom_packages', []),
            );
    }

    public function test_builder_package_catalog_page_shows_recommendations_and_service_warnings(): void
    {
        $this->withoutVite();

        $category = PackageCategory::factory()->create([
            'slug' => 'official-laravel',
        ]);
        $horizon = Package::factory()->for($category, 'category')->create([
            'name' => 'Laravel Horizon',
            'composer_name' => 'laravel/horizon',
            'is_official' => true,
            'required_services' => ['redis'],
        ]);
        $scout = Package::factory()->for($category, 'category')->create([
            'name' => 'Laravel Scout',
            'composer_name' => 'laravel/scout',
            'is_official' => true,
            'required_services' => ['meilisearch'],
        ]);
        Package::factory()->for($category, 'category')->create([
            'name' => 'Redis Tooling',
            'composer_name' => 'vendor/redis-tooling',
            'required_services' => ['redis'],
        ]);

        $this->get(route('builder.runtime'))->assertOk();
        $this->put(route('builder.runtime.update'), [
            'database' => 'mysql',
            'cache' => 'database',
            'queue' => 'redis',
            'mail' => 'mailpit',
            'search' => 'meilisearch',
            'local_runtime' => 'sail',
        ])->assertRedirect(route('builder.packages'));

        $this->get(route('builder.packages'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('recommendations', 2)
                ->where('recommendations.0.composer_name', 'laravel/horizon')
                ->where('recommendations.0.package_id', $horizon->id)
                ->where('recommendations.0.is_selected', false)
                ->where('recommendations.1.composer_name', 'laravel/scout')
                ->where('recommendations.1.package_id', $scout->id)
                ->where('recommendations.1.is_selected', false)
                ->where('packages.data.0.compatibility_warnings', [])
                ->where('packages.data.1.compatibility_warnings', []),
            );
    }

    public function test_builder_package_catalog_warns_when_required_service_is_not_enabled(): void
    {
        $this->withoutVite();

        Package::factory()->create([
            'name' => 'Redis Tooling',
            'composer_name' => 'vendor/redis-tooling',
            'required_services' => ['redis'],
        ]);

        $this->get(route('builder.packages'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('packages.data.0.compatibility_warnings.0', 'Requires redis; enable it in the runtime step or add it manually.'),
            );
    }

    public function test_curated_package_can_be_selected_into_the_current_draft_loadout(): void
    {
        $this->withoutVite();

        $package = Package::factory()->create([
            'name' => 'Laravel Permission',
            'composer_name' => 'spatie/laravel-permission',
            'install_command' => 'composer require spatie/laravel-permission',
        ]);

        $this->get(route('builder.packages'))->assertOk();

        $this
            ->from(route('builder.packages'))
            ->post(route('builder.packages.store'), [
                'package_id' => $package->id,
            ])
            ->assertRedirect(route('builder.packages'));

        $loadout = Loadout::query()->firstOrFail();
        $selection = LoadoutPackage::query()->whereBelongsTo($loadout)->firstOrFail();

        $this->assertSame($package->id, $selection->package_id);
        $this->assertFalse($selection->is_custom);
        $this->assertSame('composer require spatie/laravel-permission', $selection->package_snapshot['install_command']);
        $this->assertSame(['dev_dependency' => false], $selection->install_options);

        $this->get(route('builder.packages'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('loadout.selected_packages', 1)
                ->where('loadout.selected_packages.0.composer_name', 'spatie/laravel-permission'),
            );
    }

    public function test_development_package_defaults_to_dev_dependency_when_selected(): void
    {
        $this->withoutVite();

        $category = PackageCategory::factory()->create([
            'slug' => 'developer-experience',
        ]);
        $package = Package::factory()->for($category, 'category')->create([
            'name' => 'Laravel Debugbar',
            'composer_name' => 'barryvdh/laravel-debugbar',
            'description' => 'Add a local debugging toolbar.',
            'tags' => ['debugging', 'local-development'],
        ]);

        $this->get(route('builder.packages'))->assertOk();

        $this
            ->from(route('builder.packages'))
            ->post(route('builder.packages.store'), [
                'package_id' => $package->id,
            ])
            ->assertRedirect(route('builder.packages'));

        $selection = LoadoutPackage::query()->firstOrFail();

        $this->assertSame(['dev_dependency' => true], $selection->install_options);
    }

    public function test_recommended_official_package_can_be_selected_by_composer_name(): void
    {
        $this->withoutVite();

        $this->get(route('builder.runtime'))->assertOk();
        $this->put(route('builder.runtime.update'), [
            'database' => 'mysql',
            'cache' => 'database',
            'queue' => 'redis',
            'mail' => 'mailpit',
            'search' => 'none',
            'local_runtime' => 'sail',
        ])->assertRedirect(route('builder.packages'));

        $this
            ->from(route('builder.packages'))
            ->post(route('builder.packages.store'), [
                'composer_name' => 'laravel/horizon',
            ])
            ->assertRedirect(route('builder.packages'));

        $package = Package::query()->where('composer_name', 'laravel/horizon')->firstOrFail();
        $loadout = Loadout::query()->firstOrFail();
        $selection = LoadoutPackage::query()->whereBelongsTo($loadout)->firstOrFail();

        $this->assertSame($package->id, $selection->package_id);
        $this->assertSame('Laravel Horizon', $selection->display_name);
        $this->assertTrue($package->is_official);
        $this->assertSame(['redis'], $selection->package_snapshot['required_services']);
        $this->assertSame(['dev_dependency' => false], $selection->install_options);
    }

    public function test_selected_package_notes_and_install_options_can_be_configured(): void
    {
        $this->withoutVite();

        $package = Package::factory()->create([
            'name' => 'Laravel Debugbar',
            'composer_name' => 'barryvdh/laravel-debugbar',
        ]);

        $this->get(route('builder.packages'))->assertOk();
        $this->post(route('builder.packages.store'), [
            'package_id' => $package->id,
        ])->assertRedirect(route('builder.packages'));

        $selection = LoadoutPackage::query()->firstOrFail();

        $this
            ->from(route('builder.packages'))
            ->patch(route('builder.packages.update', $selection), [
                'install_as_dev' => '1',
                'notes' => 'Enable only in local environments.',
            ])
            ->assertRedirect(route('builder.packages'));

        $selection->refresh();

        $this->assertSame('Enable only in local environments.', $selection->notes);
        $this->assertSame(['dev_dependency' => true], $selection->install_options);

        $this->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('artifacts.0.content', fn (string $content): bool => str_contains($content, '"barryvdh/laravel-debugbar": "*"') && str_contains($content, '"require-dev"'))
                ->where('artifacts.4.content', fn (string $content): bool => str_contains($content, '- barryvdh/laravel-debugbar (dev dependency) - Enable only in local environments.')),
            );
    }

    public function test_selected_package_configuration_must_belong_to_current_draft(): void
    {
        $this->withoutVite();

        $foreignSelection = LoadoutPackage::factory()->create();

        $this->get(route('builder.packages'))->assertOk();

        $this
            ->patch(route('builder.packages.update', $foreignSelection), [
                'install_as_dev' => '0',
                'notes' => 'Not allowed',
            ])
            ->assertNotFound();
    }

    public function test_selected_package_can_be_removed_from_the_current_draft(): void
    {
        $this->withoutVite();

        $package = Package::factory()->create([
            'name' => 'Laravel Permission',
            'composer_name' => 'spatie/laravel-permission',
        ]);

        $this->get(route('builder.packages'))->assertOk();
        $this->post(route('builder.packages.store'), [
            'package_id' => $package->id,
        ])->assertRedirect(route('builder.packages'));

        $selection = LoadoutPackage::query()->firstOrFail();

        $this
            ->from(route('builder.packages'))
            ->delete(route('builder.packages.destroy', $selection))
            ->assertRedirect(route('builder.packages'));

        $this->assertDatabaseMissing('loadout_packages', [
            'id' => $selection->id,
        ]);

        $this->get(route('builder.packages'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('loadout.selected_package_ids', [])
                ->where('loadout.selected_packages', []),
            );
    }

    public function test_selected_package_removal_must_belong_to_current_draft(): void
    {
        $this->withoutVite();

        $foreignSelection = LoadoutPackage::factory()->create();

        $this->get(route('builder.packages'))->assertOk();

        $this
            ->delete(route('builder.packages.destroy', $foreignSelection))
            ->assertNotFound();

        $this->assertDatabaseHas('loadout_packages', [
            'id' => $foreignSelection->id,
        ]);
    }

    public function test_custom_package_url_is_added_only_to_the_current_draft_loadout(): void
    {
        $this->withoutVite();
        Mail::fake();

        $this->get(route('builder.packages'))->assertOk();

        $this
            ->from(route('builder.packages'))
            ->post(route('builder.custom-packages.store'), [
                'package_url' => 'https://packagist.org/packages/vendor/custom-tool',
            ])
            ->assertRedirect(route('builder.packages'));

        $loadout = Loadout::query()->firstOrFail();
        $suggestion = PackageSuggestion::query()->whereBelongsTo($loadout)->firstOrFail();
        $selection = LoadoutPackage::query()->whereBelongsTo($loadout)->firstOrFail();

        $this->assertSame('vendor/custom-tool', $suggestion->composer_name);
        $this->assertSame($suggestion->id, $selection->package_suggestion_id);
        $this->assertTrue($selection->is_custom);
        $this->assertNull($selection->package_id);
        $this->assertSame(['dev_dependency' => false], $selection->install_options);

        Mail::assertQueued(PackageSuggestionSubmitted::class, fn (PackageSuggestionSubmitted $mail) => $mail->suggestion->is($suggestion));
    }

    public function test_custom_development_package_defaults_to_dev_dependency(): void
    {
        $this->withoutVite();
        Mail::fake();

        $this->get(route('builder.packages'))->assertOk();

        $this
            ->from(route('builder.packages'))
            ->post(route('builder.custom-packages.store'), [
                'package_url' => 'https://packagist.org/packages/vendor/debug-helper',
            ])
            ->assertRedirect(route('builder.packages'));

        $selection = LoadoutPackage::query()->firstOrFail();

        $this->assertTrue($selection->is_custom);
        $this->assertSame(['dev_dependency' => true], $selection->install_options);
    }

    public function test_custom_package_url_must_be_recognized(): void
    {
        $this->withoutVite();

        $this->get(route('builder.packages'))->assertOk();

        $this
            ->from(route('builder.packages'))
            ->post(route('builder.custom-packages.store'), [
                'package_url' => 'not a package',
            ])
            ->assertSessionHasErrors('package_url')
            ->assertRedirect(route('builder.packages'));
    }
}
