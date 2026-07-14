<?php

namespace App\Http\Controllers\Builder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Builder\StoreLoadoutPackageRequest;
use App\Http\Requests\Builder\UpdateLoadoutPackageRequest;
use App\Models\LoadoutPackage;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Services\LoadoutBuilder;
use App\Services\PackageCompatibilityAdvisor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoadoutPackageController extends Controller
{
    public function store(
        StoreLoadoutPackageRequest $request,
        LoadoutBuilder $builder,
        PackageCompatibilityAdvisor $advisor,
    ): RedirectResponse {
        $validated = $request->validated();
        $loadout = $builder->currentDraft($request->session(), $request->user());
        $package = $this->packageForSelection($validated, $advisor);

        $builder->selectPackage($loadout, $package);

        return back()->with('status', 'package-selected');
    }

    public function update(UpdateLoadoutPackageRequest $request, LoadoutPackage $loadoutPackage, LoadoutBuilder $builder): RedirectResponse
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());

        abort_unless($loadoutPackage->loadout_id === $loadout->id, 404);

        $validated = $request->validated();

        $loadoutPackage->forceFill([
            'notes' => $validated['notes'] ?? null,
            'install_options' => [
                'dev_dependency' => $request->boolean('install_as_dev'),
            ],
        ])->save();

        return back()->with('status', 'package-configured');
    }

    public function destroy(Request $request, LoadoutPackage $loadoutPackage, LoadoutBuilder $builder): RedirectResponse
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());

        abort_unless($loadoutPackage->loadout_id === $loadout->id, 404);

        $loadoutPackage->delete();

        return back()->with('status', 'package-removed');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function packageForSelection(array $validated, PackageCompatibilityAdvisor $advisor): Package
    {
        if (isset($validated['package_id'])) {
            return Package::query()
                ->whereKey((int) $validated['package_id'])
                ->firstOrFail();
        }

        return $this->recommendedPackage((string) $validated['composer_name'], $advisor);
    }

    private function recommendedPackage(string $composerName, PackageCompatibilityAdvisor $advisor): Package
    {
        $composerName = Str::lower($composerName);
        $package = Package::query()
            ->where('composer_name', $composerName)
            ->first();

        if ($package) {
            return $package;
        }

        $metadata = $advisor->recommendedPackage($composerName);

        if ($metadata === null) {
            abort(404);
        }

        $category = PackageCategory::query()->firstOrCreate(
            ['slug' => 'official-laravel'],
            [
                'name' => 'Official Laravel',
                'description' => 'First-party Laravel packages for common framework features.',
                'sort_order' => 6,
                'is_active' => true,
            ],
        );

        return Package::query()->create([
            'package_category_id' => $category->id,
            'name' => $metadata['name'],
            'slug' => Str::slug($composerName),
            'composer_name' => $composerName,
            'description' => $metadata['description'],
            'source_url' => "https://github.com/{$composerName}",
            'packagist_url' => "https://packagist.org/packages/{$composerName}",
            'github_url' => "https://github.com/{$composerName}",
            'install_command' => "composer require {$composerName}",
            'post_install_commands' => $metadata['post_install_commands'] ?? [],
            'publish_commands' => $metadata['publish_commands'] ?? [],
            'required_services' => $metadata['required_services'],
            'tags' => $metadata['tags'],
            'is_official' => true,
            'is_featured' => true,
        ]);
    }
}
