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
}
