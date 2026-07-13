<?php

namespace Tests\Feature;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Models\LoadoutPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoadoutDiscoveryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_loadouts_are_discoverable(): void
    {
        $this->withoutVite();

        $author = User::factory()->create(['name' => 'Taylor Builder']);
        $publicLoadout = Loadout::factory()->for($author)->create([
            'title' => 'SaaS Starter',
            'slug' => 'saas-starter',
            'visibility' => LoadoutVisibility::Public,
            'star_count' => 12,
            'clone_count' => 4,
            'published_at' => now(),
        ]);
        LoadoutPackage::factory()->for($publicLoadout)->count(3)->create();

        Loadout::factory()->create([
            'title' => 'Unlisted Stack',
            'visibility' => LoadoutVisibility::Unlisted,
            'published_at' => now(),
        ]);
        Loadout::factory()->create([
            'title' => 'Private Stack',
            'visibility' => LoadoutVisibility::Private,
            'published_at' => null,
        ]);

        $this->get(route('discover'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('discover/index')
                ->has('loadouts', 1)
                ->where('loadouts.0.title', 'SaaS Starter')
                ->where('loadouts.0.author_name', 'Taylor Builder')
                ->where('loadouts.0.packages_count', 3)
                ->where('loadouts.0.star_count', 12)
                ->where('loadouts.0.clone_count', 4)
                ->where('stats.public_loadouts', 1)
                ->where('stats.total_stars', 12),
            );
    }

    public function test_discovery_can_search_public_loadouts(): void
    {
        $this->withoutVite();

        Loadout::factory()->create([
            'title' => 'Agency Starter',
            'description' => 'Client services toolkit',
            'visibility' => LoadoutVisibility::Public,
            'published_at' => now(),
        ]);
        Loadout::factory()->create([
            'title' => 'API Starter',
            'description' => 'Headless stack',
            'visibility' => LoadoutVisibility::Public,
            'published_at' => now(),
        ]);

        $this->get(route('discover', ['search' => 'agency']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.search', 'agency')
                ->has('loadouts', 1)
                ->where('loadouts.0.title', 'Agency Starter'),
            );
    }

    public function test_discovery_can_sort_public_loadouts(): void
    {
        $this->withoutVite();

        Loadout::factory()->create([
            'title' => 'Clone Heavy',
            'visibility' => LoadoutVisibility::Public,
            'star_count' => 1,
            'clone_count' => 20,
            'published_at' => now()->subDay(),
        ]);
        Loadout::factory()->create([
            'title' => 'Star Heavy',
            'visibility' => LoadoutVisibility::Public,
            'star_count' => 30,
            'clone_count' => 2,
            'published_at' => now(),
        ]);

        $this->get(route('discover', ['sort' => 'clones']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'clones')
                ->where('loadouts.0.title', 'Clone Heavy'),
            );
    }

    public function test_invalid_discovery_filter_is_rejected(): void
    {
        $this->get(route('discover', ['sort' => 'unsafe']))->assertSessionHasErrors('sort');
    }
}
