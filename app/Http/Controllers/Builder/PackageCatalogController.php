<?php

namespace App\Http\Controllers\Builder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Builder\PackageCatalogRequest;
use App\Models\Loadout;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Services\LoadoutBuilder;
use App\Services\PackageCatalog;
use App\Services\PackageCompatibilityAdvisor;
use Inertia\Inertia;
use Inertia\Response;

class PackageCatalogController extends Controller
{
    public function __invoke(
        PackageCatalogRequest $request,
        PackageCatalog $catalog,
        LoadoutBuilder $builder,
        PackageCompatibilityAdvisor $advisor,
    ): Response {
        $loadout = $builder->currentDraft($request->session(), $request->user());
        $selectedPackageIds = $loadout->packages()
            ->whereNotNull('package_id')
            ->pluck('package_id')
            ->all();

        $packages = $catalog
            ->search($request->filters())
            ->through(fn (Package $package): array => [
                'id' => $package->id,
                'name' => $package->name,
                'composer_name' => $package->composer_name,
                'npm_name' => $package->npm_name,
                'description' => $package->description,
                'category' => $package->category ? [
                    'name' => $package->category->name,
                    'slug' => $package->category->slug,
                ] : null,
                'tags' => $package->tags ?? [],
                'required_services' => $package->required_services ?? [],
                'is_official' => $package->is_official,
                'is_featured' => $package->is_featured,
                'stars' => $package->stars,
                'downloads' => $package->downloads,
                'usage_count' => $package->usage_count,
                'last_source_update_at' => $package->last_source_update_at?->toDateString(),
                'stats_refreshed_at' => $package->stats_refreshed_at?->toDateString(),
                'compatibility_warnings' => $advisor->warnings($package, $loadout),
            ]);

        return Inertia::render('builder/packages', [
            'packages' => $packages,
            'categories' => PackageCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['name', 'slug'])
                ->values()
                ->all(),
            'loadout' => [
                'id' => $loadout->id,
                'title' => $loadout->title,
                'selected_package_ids' => $selectedPackageIds,
                'selected_packages' => $loadout->packages()
                    ->whereNotNull('package_id')
                    ->orderBy('position')
                    ->get(['id', 'display_name', 'composer_name', 'install_options', 'notes'])
                    ->values()
                    ->all(),
                'custom_packages' => $loadout->packages()
                    ->where('is_custom', true)
                    ->orderBy('position')
                    ->get(['id', 'display_name', 'composer_name', 'source_url'])
                    ->values()
                    ->all(),
            ],
            'recommendations' => $this->recommendations($advisor, $loadout, $selectedPackageIds),
            'filters' => (object) $request->filters(),
        ]);
    }

    /**
     * @param  array<int, int>  $selectedPackageIds
     * @return array<int, array{composer_name: string, title: string, reason: string, package_id: int|null, is_selected: bool}>
     */
    private function recommendations(PackageCompatibilityAdvisor $advisor, Loadout $loadout, array $selectedPackageIds): array
    {
        $recommendations = $advisor->recommendations($loadout);
        $packages = Package::query()
            ->whereIn('composer_name', collect($recommendations)->pluck('composer_name')->all())
            ->get(['id', 'composer_name'])
            ->keyBy('composer_name');

        return collect($recommendations)
            ->map(function (array $recommendation) use ($packages, $selectedPackageIds): array {
                $package = $packages->get($recommendation['composer_name']);
                $packageId = $package?->id;

                return [
                    ...$recommendation,
                    'package_id' => $packageId,
                    'is_selected' => $packageId !== null && in_array($packageId, $selectedPackageIds, true),
                ];
            })
            ->values()
            ->all();
    }
}
