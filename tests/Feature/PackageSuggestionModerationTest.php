<?php

namespace Tests\Feature;

use App\Enums\PackageSuggestionStatus;
use App\Mail\PackageSuggestionSubmitted;
use App\Models\LoadoutPackage;
use App\Models\Package;
use App\Models\PackageSuggestion;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PackageSuggestionModerationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_package_suggestion_email_contains_signed_moderation_links(): void
    {
        $suggestion = PackageSuggestion::factory()->create([
            'composer_name' => 'vendor/custom-tool',
            'display_name' => 'Custom Tool',
        ]);

        $mailable = new PackageSuggestionSubmitted($suggestion);

        $mailable->assertSeeInHtml('vendor/custom-tool');
        $mailable->assertSeeInHtml('/package-suggestions/'.$suggestion->id.'/approve');
        $mailable->assertSeeInHtml('/package-suggestions/'.$suggestion->id.'/ignore');
        $mailable->assertSeeInHtml('signature=');
    }

    public function test_signed_approve_link_promotes_package_suggestion_to_curated_package(): void
    {
        $suggestion = PackageSuggestion::factory()->create([
            'composer_name' => 'vendor/custom-tool',
            'display_name' => 'Custom Tool',
            'source_url' => 'https://packagist.org/packages/vendor/custom-tool',
            'packagist_url' => 'https://packagist.org/packages/vendor/custom-tool',
        ]);
        $selection = LoadoutPackage::factory()->for($suggestion, 'packageSuggestion')->create([
            'loadout_id' => $suggestion->loadout_id,
            'package_id' => null,
            'composer_name' => 'vendor/custom-tool',
            'is_custom' => true,
        ]);

        $this->get(URL::temporarySignedRoute(
            'package-suggestions.approve',
            now()->addDays(7),
            ['package_suggestion' => $suggestion],
        ))->assertRedirect(route('builder.packages'));

        $suggestion->refresh();
        $selection->refresh();
        $package = Package::query()->where('composer_name', 'vendor/custom-tool')->firstOrFail();

        $this->assertSame(PackageSuggestionStatus::Approved, $suggestion->status);
        $this->assertSame($package->id, $suggestion->approved_package_id);
        $this->assertSame($package->id, $selection->package_id);
        $this->assertSame('composer require vendor/custom-tool', $package->install_command);
    }

    public function test_signed_ignore_link_marks_package_suggestion_ignored(): void
    {
        $suggestion = PackageSuggestion::factory()->create([
            'composer_name' => 'vendor/custom-tool',
        ]);

        $this->get(URL::temporarySignedRoute(
            'package-suggestions.ignore',
            now()->addDays(7),
            ['package_suggestion' => $suggestion],
        ))->assertRedirect(route('builder.packages'));

        $suggestion->refresh();

        $this->assertSame(PackageSuggestionStatus::Ignored, $suggestion->status);
        $this->assertNotNull($suggestion->moderated_at);
    }

    public function test_unsigned_moderation_links_are_rejected(): void
    {
        $suggestion = PackageSuggestion::factory()->create();

        $this
            ->get(route('package-suggestions.approve', $suggestion))
            ->assertForbidden();
    }
}
