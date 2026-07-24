<?php

namespace Tests\Feature;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Models\LoadoutCollaborator;
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
                ->where('loadouts.0.access_role', 'owner')
                ->where('loadouts.0.can_edit', true)
                ->where('loadouts.0.is_locked', true)
                ->where('loadouts.0.can_resume', false)
                ->where('loadouts.0.can_manage_collaborators', true)
                ->where('loadouts.1.id', $olderLoadout->id),
            );
    }

    public function test_user_can_view_collaborated_loadouts_in_library(): void
    {
        $this->withoutVite();

        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $editor = User::factory()->create();
        $viewerLoadout = Loadout::factory()->for($owner)->create([
            'title' => 'Viewer Stack',
            'updated_at' => now(),
        ]);
        $editorLoadout = Loadout::factory()->for($owner)->create([
            'title' => 'Editor Stack',
            'updated_at' => now()->subMinute(),
        ]);
        LoadoutCollaborator::query()->create([
            'loadout_id' => $viewerLoadout->id,
            'user_id' => $viewer->id,
            'role' => 'viewer',
        ]);
        LoadoutCollaborator::query()->create([
            'loadout_id' => $editorLoadout->id,
            'user_id' => $editor->id,
            'role' => 'editor',
        ]);

        $this->actingAs($viewer)
            ->get(route('loadouts.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('loadouts', 1)
                ->where('loadouts.0.title', 'Viewer Stack')
                ->where('loadouts.0.access_role', 'viewer')
                ->where('loadouts.0.can_edit', false)
                ->where('loadouts.0.can_manage_collaborators', false),
            );

        $this->actingAs($editor)
            ->get(route('loadouts.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('loadouts', 1)
                ->where('loadouts.0.title', 'Editor Stack')
                ->where('loadouts.0.access_role', 'editor')
                ->where('loadouts.0.can_edit', true)
                ->where('loadouts.0.can_manage_collaborators', false),
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
            'current_step' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('loadouts.resume', $loadout))
            ->assertRedirect(route('builder.project'));

        $this->actingAs($user)
            ->get(route('builder.review'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('loadout.id', $loadout->id)
                ->where('loadout.title', 'Agency Stack'),
            );
    }

    public function test_user_resumes_a_partially_configured_loadout_at_its_saved_step(): void
    {
        $user = User::factory()->create();
        $loadout = Loadout::factory()->for($user)->create([
            'current_step' => 3,
        ]);

        $this->actingAs($user)
            ->post(route('loadouts.resume', $loadout))
            ->assertRedirect(route('builder.runtime'));
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

    public function test_locked_loadout_cannot_be_resumed(): void
    {
        $user = User::factory()->create();
        $loadout = Loadout::factory()->for($user)->create([
            'visibility' => LoadoutVisibility::Unlisted,
            'published_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('loadouts.resume', $loadout))
            ->assertForbidden();
    }

    public function test_editor_collaborator_can_resume_and_edit_loadout(): void
    {
        $this->withoutVite();

        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $loadout = Loadout::factory()->for($owner)->create([
            'title' => 'Shared Stack',
            'slug' => 'shared-stack',
            'current_step' => 1,
        ]);
        LoadoutCollaborator::query()->create([
            'loadout_id' => $loadout->id,
            'user_id' => $editor->id,
            'role' => 'editor',
        ]);

        $this->actingAs($editor)
            ->post(route('loadouts.resume', $loadout))
            ->assertRedirect(route('builder.project'));

        $this->actingAs($editor)
            ->put(route('builder.project.update'), [
                'project_name' => 'Edited Shared Stack',
                'app_name' => 'Shared App',
                'description' => 'Updated by an editor collaborator.',
                'php_version' => '8.5',
                'laravel_version' => '13.x',
                'package_manager' => 'yarn',
            ])
            ->assertRedirect(route('builder.starter'));

        $this->assertSame('Edited Shared Stack', $loadout->refresh()->title);
    }

    public function test_viewer_collaborator_cannot_resume_loadout(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $loadout = Loadout::factory()->for($owner)->create();
        LoadoutCollaborator::query()->create([
            'loadout_id' => $loadout->id,
            'user_id' => $viewer->id,
            'role' => 'viewer',
        ]);

        $this->actingAs($viewer)
            ->post(route('loadouts.resume', $loadout))
            ->assertNotFound();
    }
}
