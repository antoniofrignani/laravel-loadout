<?php

namespace Tests\Feature;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Models\LoadoutPackage;
use App\Models\Package;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoadoutSharingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_current_draft_loadout_can_be_published_as_unlisted_share_link(): void
    {
        $this->withoutVite();

        $this->get(route('builder.review'))->assertOk();

        $this
            ->from(route('builder.review'))
            ->post(route('builder.publish'))
            ->assertRedirect(route('builder.review'));

        $loadout = Loadout::query()->firstOrFail();

        $this->assertSame(LoadoutVisibility::Unlisted, $loadout->visibility);
        $this->assertNotNull($loadout->published_at);

        $this->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('loadout.is_shareable', true)
                ->where('loadout.share_url', route('loadouts.show', $loadout)),
            );
    }

    public function test_unlisted_loadout_share_page_is_publicly_visible(): void
    {
        $this->withoutVite();

        $package = Package::factory()->create([
            'name' => 'Laravel Permission',
            'composer_name' => 'spatie/laravel-permission',
        ]);
        $loadout = Loadout::factory()->create([
            'title' => 'Agency Starter',
            'slug' => 'agency-starter',
            'visibility' => LoadoutVisibility::Unlisted,
            'published_at' => now(),
        ]);
        LoadoutPackage::factory()->for($loadout)->for($package)->create([
            'display_name' => 'Laravel Permission',
            'composer_name' => 'spatie/laravel-permission',
        ]);

        $this->get(route('loadouts.show', $loadout))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('loadouts/show')
                ->where('loadout.title', 'Agency Starter')
                ->where('loadout.share_url', route('loadouts.show', $loadout))
                ->has('packages', 1)
                ->where('packages.0.composer_name', 'spatie/laravel-permission')
                ->has('artifacts', 5),
            );
    }

    public function test_private_loadout_share_page_is_not_visible(): void
    {
        $loadout = Loadout::factory()->create([
            'visibility' => LoadoutVisibility::Private,
        ]);

        $this->get(route('loadouts.show', $loadout))->assertNotFound();
        $this->get(route('loadouts.download', $loadout))->assertNotFound();
    }

    public function test_shared_loadout_can_be_downloaded_as_zip(): void
    {
        $loadout = Loadout::factory()->create([
            'slug' => 'agency-starter',
            'visibility' => LoadoutVisibility::Unlisted,
            'published_at' => now(),
        ]);

        $this
            ->get(route('loadouts.download', $loadout))
            ->assertDownload('laravel-loadout-agency-starter.zip');
    }

    public function test_shared_loadout_can_be_cloned_into_current_builder_session(): void
    {
        $this->withoutVite();

        $package = Package::factory()->create([
            'name' => 'Laravel Permission',
            'composer_name' => 'spatie/laravel-permission',
        ]);
        $source = Loadout::factory()->create([
            'title' => 'Agency Starter',
            'slug' => 'agency-starter',
            'description' => 'A useful agency stack.',
            'visibility' => LoadoutVisibility::Unlisted,
            'current_step' => 4,
            'builder_state' => [
                'project' => [
                    'name' => 'Agency Starter',
                    'laravel_version' => '13.x',
                    'php_version' => '8.5',
                ],
            ],
            'published_at' => now(),
        ]);
        LoadoutPackage::factory()->for($source)->for($package)->create([
            'display_name' => 'Laravel Permission',
            'composer_name' => 'spatie/laravel-permission',
            'install_options' => ['dev_dependency' => true],
            'notes' => 'Publish permissions config.',
            'position' => 2,
        ]);

        $this
            ->from(route('loadouts.show', $source))
            ->post(route('loadouts.clone', $source))
            ->assertRedirect(route('builder.review'));

        $source->refresh();
        $clone = Loadout::query()
            ->where('source_loadout_id', $source->id)
            ->sole();

        $this->assertSame(1, $source->clone_count);
        $this->assertSame('Copy of Agency Starter', $clone->title);
        $this->assertSame(LoadoutVisibility::Private, $clone->visibility);
        $this->assertSame(6, $clone->current_step);
        $this->assertSame('A useful agency stack.', $clone->description);
        $this->assertSame($source->builder_state, $clone->builder_state);

        $clonedPackage = $clone->packages()->sole();

        $this->assertSame($package->id, $clonedPackage->package_id);
        $this->assertSame('spatie/laravel-permission', $clonedPackage->composer_name);
        $this->assertSame(['dev_dependency' => true], $clonedPackage->install_options);
        $this->assertSame('Publish permissions config.', $clonedPackage->notes);

        $this->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('loadout.title', 'Copy of Agency Starter')
                ->where('loadout.is_shareable', false)
                ->has('artifacts', 5)
                ->where('artifacts.0.content', fn (string $content) => str_contains($content, 'spatie/laravel-permission')),
            );
    }

    public function test_private_loadout_cannot_be_cloned(): void
    {
        $loadout = Loadout::factory()->create([
            'visibility' => LoadoutVisibility::Private,
            'clone_count' => 0,
        ]);

        $this->post(route('loadouts.clone', $loadout))->assertNotFound();

        $this->assertSame(0, $loadout->refresh()->clone_count);
        $this->assertFalse(Loadout::query()->where('source_loadout_id', $loadout->id)->exists());
    }
}
