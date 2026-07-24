<?php

namespace App\Services;

use App\Enums\PackageSuggestionStatus;
use App\Models\Loadout;
use App\Models\LoadoutPackage;
use App\Models\Package;
use App\Models\PackageSuggestion;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Str;

class LoadoutBuilder
{
    private const SessionKey = 'laravel_loadout.current_loadout_id';

    public function __construct(
        private LoadoutAccess $access,
        private PackageInstallClassifier $installClassifier,
    ) {}

    public function useDraft(Session $session, Loadout $loadout): void
    {
        $session->put(self::SessionKey, $loadout->id);
    }

    public function ensureEditable(Loadout $loadout): void
    {
        abort_if($loadout->isLocked(), 403, 'This loadout is locked after its share link was created. Clone it to make changes.');
    }

    public function currentDraft(Session $session, ?User $user = null): Loadout
    {
        $loadoutId = $session->get(self::SessionKey);

        if (is_int($loadoutId)) {
            $loadout = Loadout::query()->whereKey($loadoutId)->first();

            if ($loadout) {
                if (! $user && ! $loadout->user_id) {
                    return $loadout;
                }

                if ($user && ! $loadout->user_id) {
                    $loadout->forceFill(['user_id' => $user->id])->save();

                    return $loadout;
                }

                if ($user && $this->access->canEdit($user, $loadout)) {
                    return $loadout;
                }

                $session->forget(self::SessionKey);
            }
        }

        return $this->startNewDraft($session, $user);
    }

    public function startNewDraft(Session $session, ?User $user = null): Loadout
    {
        $loadout = Loadout::query()->create([
            'user_id' => $user?->id,
            'title' => 'Untitled Loadout',
            'slug' => $this->uniqueSlug('untitled-loadout'),
            'description' => 'Draft Laravel starter stack.',
            'builder_state' => [],
        ]);

        $this->useDraft($session, $loadout);

        return $loadout;
    }

    public function selectPackage(Loadout $loadout, Package $package, ?string $notes = null): LoadoutPackage
    {
        $selection = LoadoutPackage::query()->firstOrNew([
            'loadout_id' => $loadout->id,
            'package_id' => $package->id,
        ]);

        $isNewSelection = ! $selection->exists;
        $installOptions = $selection->install_options ?: $this->installClassifier->optionsFor($package);

        $selection->fill([
            'display_name' => $package->name,
            'composer_name' => $package->composer_name,
            'npm_name' => $package->npm_name,
            'source_url' => $package->source_url,
            'is_custom' => false,
            'package_snapshot' => $this->snapshotPackage($package),
            'install_options' => $installOptions,
            'position' => $selection->position ?: $this->nextPackagePosition($loadout),
        ])->save();

        if ($notes && ! $selection->notes) {
            $selection->forceFill(['notes' => $notes])->save();
        }

        if ($isNewSelection) {
            $package->increment('usage_count');
        }

        return $selection;
    }

    /**
     * @param  array{input: string, composer_name: string, display_name: string, source_url: string}  $package
     */
    public function addCustomPackage(Loadout $loadout, array $package, ?User $user = null): LoadoutPackage
    {
        $suggestion = PackageSuggestion::query()->create([
            'user_id' => $user?->id,
            'loadout_id' => $loadout->id,
            'input' => $package['input'],
            'display_name' => $package['display_name'],
            'composer_name' => $package['composer_name'],
            'source_url' => $package['source_url'],
            'packagist_url' => "https://packagist.org/packages/{$package['composer_name']}",
            'status' => PackageSuggestionStatus::Pending,
            'lookup_payload' => [
                'composer_name' => $package['composer_name'],
                'source_url' => $package['source_url'],
            ],
        ]);

        return LoadoutPackage::query()->create([
            'loadout_id' => $loadout->id,
            'package_suggestion_id' => $suggestion->id,
            'display_name' => $package['display_name'],
            'composer_name' => $package['composer_name'],
            'source_url' => $package['source_url'],
            'is_custom' => true,
            'package_snapshot' => [
                'display_name' => $package['display_name'],
                'composer_name' => $package['composer_name'],
                'source_url' => $package['source_url'],
                'install_command' => "composer require {$package['composer_name']}",
            ],
            'install_options' => $this->installClassifier->optionsForCustomPackage($package),
            'position' => $this->nextPackagePosition($loadout),
        ]);
    }

    /**
     * @return array{input: string, composer_name: string, display_name: string, source_url: string}|null
     */
    public function parsePackageInput(string $input): ?array
    {
        $input = trim($input);

        if (preg_match('/^[a-z0-9_.-]+\/[a-z0-9_.-]+$/i', $input) === 1) {
            return [
                'input' => $input,
                'composer_name' => Str::lower($input),
                'display_name' => Str::headline(Str::after($input, '/')),
                'source_url' => "https://packagist.org/packages/{$input}",
            ];
        }

        $host = parse_url($input, PHP_URL_HOST);
        $path = trim((string) parse_url($input, PHP_URL_PATH), '/');

        if (! is_string($host) || $path === '') {
            return null;
        }

        $segments = explode('/', $path);

        if (Str::contains($host, 'packagist.org') && $segments[0] === 'packages') {
            $segments = array_slice($segments, 1);
        }

        if (count($segments) < 2) {
            return null;
        }

        $composerName = Str::lower($segments[0].'/'.$segments[1]);

        return [
            'input' => $input,
            'composer_name' => $composerName,
            'display_name' => Str::headline($segments[1]),
            'source_url' => $input,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshotPackage(Package $package): array
    {
        return [
            'id' => $package->id,
            'name' => $package->name,
            'composer_name' => $package->composer_name,
            'npm_name' => $package->npm_name,
            'description' => $package->description,
            'source_url' => $package->source_url,
            'packagist_url' => $package->packagist_url,
            'github_url' => $package->github_url,
            'install_command' => $package->install_command,
            'post_install_commands' => $package->post_install_commands ?? [],
            'publish_commands' => $package->publish_commands ?? [],
            'migration_commands' => $package->migration_commands ?? [],
            'required_services' => $package->required_services ?? [],
        ];
    }

    private function nextPackagePosition(Loadout $loadout): int
    {
        return $loadout->packages()->count();
    }

    private function uniqueSlug(string $base): string
    {
        do {
            $slug = $base.'-'.Str::lower(Str::random(8));
        } while (Loadout::query()->where('slug', $slug)->exists());

        return $slug;
    }
}
