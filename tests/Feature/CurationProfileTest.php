<?php

namespace Tests\Feature;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Models\LoadoutPackage;
use App\Models\PackageCollection;
use App\Models\PackageCollectionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CurationProfileTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_curation_profile_shows_public_loadouts_and_stats(): void
    {
        $this->withoutVite();

        $user = User::factory()->create([
            'name' => 'Taylor Curator',
            'profile_slug' => 'taylor-curator',
            'profile_bio' => 'Practical Laravel stacks for teams.',
        ]);
        $publicLoadout = Loadout::factory()->for($user)->create([
            'title' => 'SaaS Starter',
            'visibility' => LoadoutVisibility::Public,
            'star_count' => 8,
            'clone_count' => 3,
            'published_at' => now(),
        ]);
        LoadoutPackage::factory()->for($publicLoadout)->count(2)->create();
        Loadout::factory()->for($user)->create([
            'title' => 'Private Stack',
            'visibility' => LoadoutVisibility::Private,
            'published_at' => null,
        ]);
        $publicCollection = PackageCollection::factory()->for($user)->public()->create([
            'title' => 'Backend Picks',
            'slug' => 'backend-picks',
        ]);
        PackageCollectionItem::factory()->for($publicCollection, 'collection')->count(2)->create();
        PackageCollection::factory()->for($user)->create([
            'title' => 'Private Picks',
            'slug' => 'private-picks',
        ]);

        $this->get(route('profiles.show', ['user' => $user->profile_slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('profiles/show')
                ->where('profile.name', 'Taylor Curator')
                ->where('profile.profile_slug', 'taylor-curator')
                ->where('profile.profile_bio', 'Practical Laravel stacks for teams.')
                ->has('loadouts', 1)
                ->where('loadouts.0.title', 'SaaS Starter')
                ->where('loadouts.0.packages_count', 2)
                ->has('collections', 1)
                ->where('collections.0.title', 'Backend Picks')
                ->where('collections.0.items_count', 2)
                ->where('stats.public_loadouts', 1)
                ->where('stats.public_collections', 1)
                ->where('stats.total_stars', 8)
                ->where('stats.total_clones', 3),
            );
    }

    public function test_profile_slug_must_exist(): void
    {
        $this->get('/u/missing-curator')->assertNotFound();
    }
}
