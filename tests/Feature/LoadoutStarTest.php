<?php

namespace Tests\Feature;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Models\LoadoutStar;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoadoutStarTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_when_starring_loadout(): void
    {
        $loadout = Loadout::factory()->create([
            'visibility' => LoadoutVisibility::Unlisted,
        ]);

        $this->post(route('loadouts.stars.store', $loadout))->assertRedirect(route('login'));
    }

    public function test_user_can_star_shared_loadout_once(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $loadout = Loadout::factory()->create([
            'visibility' => LoadoutVisibility::Unlisted,
            'star_count' => 0,
            'published_at' => now(),
        ]);

        $this->actingAs($user)
            ->from(route('loadouts.show', $loadout))
            ->post(route('loadouts.stars.store', $loadout))
            ->assertRedirect(route('loadouts.show', $loadout));

        $this->actingAs($user)
            ->post(route('loadouts.stars.store', $loadout))
            ->assertRedirect();

        $this->assertSame(1, $loadout->refresh()->star_count);
        $this->assertSame(1, LoadoutStar::query()->count());

        $this->actingAs($user)
            ->get(route('loadouts.show', $loadout))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('loadout.star_count', 1)
                ->where('loadout.viewer_has_starred', true)
                ->where('loadout.can_star', true),
            );
    }

    public function test_user_can_unstar_shared_loadout(): void
    {
        $user = User::factory()->create();
        $loadout = Loadout::factory()->create([
            'visibility' => LoadoutVisibility::Public,
            'star_count' => 1,
            'published_at' => now(),
        ]);
        LoadoutStar::query()->create([
            'loadout_id' => $loadout->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete(route('loadouts.stars.destroy', $loadout))
            ->assertRedirect();

        $this->assertSame(0, $loadout->refresh()->star_count);
        $this->assertSame(0, LoadoutStar::query()->count());
    }

    public function test_user_cannot_star_private_loadout(): void
    {
        $user = User::factory()->create();
        $loadout = Loadout::factory()->create([
            'visibility' => LoadoutVisibility::Private,
        ]);

        $this->actingAs($user)
            ->post(route('loadouts.stars.store', $loadout))
            ->assertNotFound();
    }

    public function test_user_cannot_star_their_own_loadout(): void
    {
        $user = User::factory()->create();
        $loadout = Loadout::factory()->for($user)->create([
            'visibility' => LoadoutVisibility::Public,
        ]);

        $this->actingAs($user)
            ->post(route('loadouts.stars.store', $loadout))
            ->assertUnprocessable();

        $this->assertSame(0, $loadout->refresh()->star_count);
    }
}
