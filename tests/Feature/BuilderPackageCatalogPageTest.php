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

    public function test_builder_package_catalog_page_shows_recommendations_and_service_warnings(): void
    {
        $this->withoutVite();

        $category = PackageCategory::factory()->create([
            'slug' => 'official-laravel',
        ]);
        Package::factory()->for($category, 'category')->create([
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
                ->where('recommendations.1.composer_name', 'laravel/scout')
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

        $this->get(route('builder.packages'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('loadout.selected_packages', 1)
                ->where('loadout.selected_packages.0.composer_name', 'spatie/laravel-permission'),
            );
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

        Mail::assertQueued(PackageSuggestionSubmitted::class, fn (PackageSuggestionSubmitted $mail) => $mail->suggestion->is($suggestion));
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
