<?php

namespace Tests\Feature;

use App\Enums\PackageSuggestionStatus;
use App\Models\GeneratedArtifact;
use App\Models\Loadout;
use App\Models\LoadoutPackage;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageSuggestion;
use App\Models\User;
use Database\Seeders\PackageCatalogSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LoadoutCatalogTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_package_catalog_seeder_creates_curated_packages(): void
    {
        $this->seed(PackageCatalogSeeder::class);

        $this->assertTrue(PackageCategory::query()->where('slug', 'developer-experience')->exists());
        $this->assertTrue(Package::curated()->where('composer_name', 'spatie/laravel-permission')->exists());
        $this->assertTrue(Package::curated()->where('composer_name', 'filament/filament')->exists());
    }

    public function test_loadout_stores_selected_package_snapshot_and_generated_artifacts(): void
    {
        $user = User::factory()->create();
        $package = Package::factory()->create([
            'composer_name' => 'spatie/laravel-permission',
            'install_command' => 'composer require spatie/laravel-permission',
        ]);
        $loadout = Loadout::factory()->for($user)->create([
            'title' => 'SaaS Starter',
            'slug' => 'saas-starter',
        ]);

        $selection = LoadoutPackage::factory()->for($loadout)->for($package)->create([
            'display_name' => $package->name,
            'composer_name' => $package->composer_name,
            'package_snapshot' => [
                'name' => $package->name,
                'composer_name' => $package->composer_name,
                'install_command' => $package->install_command,
            ],
        ]);
        $artifact = GeneratedArtifact::factory()->for($loadout)->create([
            'filename' => 'composer.json',
            'content' => '{"require":{"spatie/laravel-permission":"^6.0"}}',
            'content_hash' => hash('sha256', '{"require":{"spatie/laravel-permission":"^6.0"}}'),
        ]);

        $this->assertTrue($user->loadouts()->whereKey($loadout)->exists());
        $this->assertTrue($loadout->packages()->whereKey($selection)->exists());
        $this->assertTrue($loadout->generatedArtifacts()->whereKey($artifact)->exists());
        $this->assertSame('composer require spatie/laravel-permission', $selection->package_snapshot['install_command']);
    }

    public function test_custom_package_suggestion_is_scoped_to_current_loadout(): void
    {
        $user = User::factory()->create();
        $loadout = Loadout::factory()->for($user)->create();
        $suggestion = PackageSuggestion::factory()->for($user)->for($loadout)->create([
            'input' => 'https://packagist.org/packages/vendor/custom-tool',
            'composer_name' => 'vendor/custom-tool',
            'status' => PackageSuggestionStatus::Pending,
        ]);

        $selection = LoadoutPackage::factory()->custom()->for($loadout)->create([
            'package_suggestion_id' => $suggestion->id,
            'display_name' => 'Custom Tool',
            'composer_name' => 'vendor/custom-tool',
            'package_snapshot' => [
                'composer_name' => 'vendor/custom-tool',
                'source_url' => 'https://packagist.org/packages/vendor/custom-tool',
            ],
        ]);

        $this->assertModelExists($suggestion);
        $this->assertTrue($suggestion->loadout->is($loadout));
        $this->assertTrue($selection->is_custom);
        $this->assertNull($selection->package_id);
        $this->assertSame(PackageSuggestionStatus::Pending, $suggestion->status);
    }
}
