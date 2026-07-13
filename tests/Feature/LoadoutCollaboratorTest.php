<?php

namespace Tests\Feature;

use App\Models\Loadout;
use App\Models\LoadoutCollaborator;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoadoutCollaboratorTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_view_loadout_collaborators(): void
    {
        $this->withoutVite();

        $owner = User::factory()->create();
        $teammate = User::factory()->create([
            'name' => 'Team Mate',
            'email' => 'teammate@example.com',
        ]);
        $loadout = Loadout::factory()->for($owner)->create([
            'title' => 'Agency Stack',
        ]);
        LoadoutCollaborator::query()->create([
            'loadout_id' => $loadout->id,
            'user_id' => $teammate->id,
            'role' => 'editor',
        ]);

        $this->actingAs($owner)
            ->get(route('loadouts.collaborators.index', $loadout))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('loadouts/collaborators')
                ->where('loadout.title', 'Agency Stack')
                ->has('collaborators', 1)
                ->where('collaborators.0.role', 'editor')
                ->where('collaborators.0.user.email', 'teammate@example.com'),
            );
    }

    public function test_owner_can_add_and_update_collaborator_role(): void
    {
        $owner = User::factory()->create();
        $teammate = User::factory()->create([
            'email' => 'teammate@example.com',
        ]);
        $loadout = Loadout::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->post(route('loadouts.collaborators.store', $loadout), [
                'email' => 'teammate@example.com',
                'role' => 'viewer',
            ])
            ->assertRedirect();

        $collaborator = LoadoutCollaborator::query()
            ->where('loadout_id', $loadout->id)
            ->where('user_id', $teammate->id)
            ->sole();

        $this->assertSame('viewer', $collaborator->role);

        $this->actingAs($owner)
            ->post(route('loadouts.collaborators.store', $loadout), [
                'email' => 'teammate@example.com',
                'role' => 'editor',
            ])
            ->assertRedirect();

        $this->assertSame('editor', $collaborator->refresh()->role);
        $this->assertSame(1, LoadoutCollaborator::query()->count());
    }

    public function test_owner_can_remove_collaborator(): void
    {
        $owner = User::factory()->create();
        $teammate = User::factory()->create();
        $loadout = Loadout::factory()->for($owner)->create();
        $collaborator = LoadoutCollaborator::query()->create([
            'loadout_id' => $loadout->id,
            'user_id' => $teammate->id,
            'role' => 'viewer',
        ]);

        $this->actingAs($owner)
            ->delete(route('loadouts.collaborators.destroy', [$loadout, $collaborator]))
            ->assertRedirect();

        $this->assertModelMissing($collaborator);
    }

    public function test_non_owner_cannot_manage_collaborators(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $loadout = Loadout::factory()->for($owner)->create();

        $this->actingAs($otherUser)
            ->get(route('loadouts.collaborators.index', $loadout))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->post(route('loadouts.collaborators.store', $loadout), [
                'email' => $otherUser->email,
                'role' => 'viewer',
            ])
            ->assertNotFound();
    }

    public function test_owner_cannot_add_self_as_collaborator(): void
    {
        $owner = User::factory()->create();
        $loadout = Loadout::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->post(route('loadouts.collaborators.store', $loadout), [
                'email' => $owner->email,
                'role' => 'editor',
            ])
            ->assertUnprocessable();
    }

    public function test_collaborator_role_must_be_supported(): void
    {
        $owner = User::factory()->create();
        $teammate = User::factory()->create();
        $loadout = Loadout::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->post(route('loadouts.collaborators.store', $loadout), [
                'email' => $teammate->email,
                'role' => 'admin',
            ])
            ->assertSessionHasErrors('role');
    }
}
