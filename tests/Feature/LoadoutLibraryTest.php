<?php

namespace Tests\Feature;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Models\LoadoutPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoadoutLibraryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_cannot_view_loadout_library(): void
    {
        $this->get(route('loadouts.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_only_their_loadouts(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Loadout::factory()->for($otherUser)->create([
            'title' => 'Hidden Team Stack',
        ]);
        $olderLoadout = Loadout::factory()->for($user)->create([
            'title' => 'Older Stack',
            'visibility' => LoadoutVisibility::Private,
            'updated_at' => now()->subDay(),
        ]);
        $newerLoadout = Loadout::factory()->for($user)->create([
            'title' => 'Published Stack',
            'visibility' => LoadoutVisibility::Unlisted,
            'published_at' => now(),
            'updated_at' => now(),
        ]);
        LoadoutPackage::factory()->for($newerLoadout)->count(2)->create();

        $this->actingAs($user)
            ->get(route('loadouts.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('loadouts/index')
                ->has('loadouts', 2)
                ->where('loadouts.0.title', 'Published Stack')
                ->where('loadouts.0.visibility', 'unlisted')
                ->where('loadouts.0.packages_count', 2)
                ->where('loadouts.1.id', $olderLoadout->id),
            );
    }

    public function test_user_can_resume_owned_loadout_into_builder_session(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $loadout = Loadout::factory()->for($user)->create([
            'title' => 'Agency Stack',
            'slug' => 'agency-stack',
            'visibility' => LoadoutVisibility::Private,
        ]);

        $this->actingAs($user)
            ->post(route('loadouts.resume', $loadout))
            ->assertRedirect(route('builder.review'));

        $this->actingAs($user)
            ->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('loadout.id', $loadout->id)
                ->where('loadout.title', 'Agency Stack'),
            );
    }

    public function test_user_cannot_resume_another_users_loadout(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $loadout = Loadout::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->post(route('loadouts.resume', $loadout))
            ->assertNotFound();
    }
}
