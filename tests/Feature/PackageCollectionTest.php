<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackageCollection;
use App\Models\PackageCollectionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PackageCollectionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_create_and_view_package_collections(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('package-collections.store'), [
                'title' => 'SaaS Essentials',
                'description' => 'Packages for a production SaaS starter.',
                'visibility' => 'public',
            ])
            ->assertRedirect(route('package-collections.show', 'saas-essentials'));

        $this->assertDatabaseHas('package_collections', [
            'user_id' => $user->id,
            'title' => 'SaaS Essentials',
            'slug' => 'saas-essentials',
            'visibility' => 'public',
        ]);

        $this->actingAs($user)
            ->get(route('package-collections.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('package-collections/index')
                ->has('collections', 1)
                ->where('collections.0.title', 'SaaS Essentials')
                ->where('collections.0.items_count', 0),
            );
    }

    public function test_public_collection_is_visible_and_private_collection_is_owner_only(): void
    {
        $this->withoutVite();

        $owner = User::factory()->create(['name' => 'Taylor Curator']);
        $publicCollection = PackageCollection::factory()->for($owner)->public()->create([
            'title' => 'Public Stack',
            'slug' => 'public-stack',
        ]);
        $privateCollection = PackageCollection::factory()->for($owner)->create([
            'title' => 'Private Stack',
            'slug' => 'private-stack',
        ]);

        $this->get(route('package-collections.show', $publicCollection))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('package-collections/show')
                ->where('collection.title', 'Public Stack')
                ->where('collection.can_manage', false)
                ->where('collection.curator.name', 'Taylor Curator'),
            );

        $this->get(route('package-collections.show', $privateCollection))
            ->assertNotFound();

        $this->actingAs($owner)
            ->get(route('package-collections.show', $privateCollection))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('collection.title', 'Private Stack')
                ->where('collection.can_manage', true),
            );
    }

    public function test_owner_can_add_and_remove_packages_from_a_collection(): void
    {
        $this->withoutVite();

        $owner = User::factory()->create();
        $collection = PackageCollection::factory()->for($owner)->create([
            'slug' => 'backend-picks',
        ]);
        $package = Package::factory()->create([
            'name' => 'Laravel Horizon',
            'composer_name' => 'laravel/horizon',
        ]);

        $this->actingAs($owner)
            ->post(route('package-collections.items.store', $collection), [
                'package_id' => $package->id,
                'notes' => 'Useful for queued workloads.',
            ])
            ->assertRedirect();

        $item = PackageCollectionItem::query()->where([
            'package_collection_id' => $collection->id,
            'package_id' => $package->id,
        ])->firstOrFail();

        $this->assertSame('Useful for queued workloads.', $item->notes);

        $this->actingAs($owner)
            ->delete(route('package-collections.items.destroy', [$collection, $item]))
            ->assertRedirect();

        $this->assertModelMissing($item);
    }

    public function test_non_owner_cannot_mutate_collection_items(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $collection = PackageCollection::factory()->for($owner)->create();
        $package = Package::factory()->create();

        $this->actingAs($otherUser)
            ->post(route('package-collections.items.store', $collection), [
                'package_id' => $package->id,
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('package_collection_items', [
            'package_collection_id' => $collection->id,
            'package_id' => $package->id,
        ]);
    }
}
