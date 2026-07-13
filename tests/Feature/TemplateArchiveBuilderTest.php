<?php

namespace Tests\Feature;

use App\Models\Loadout;
use App\Models\LoadoutPackage;
use App\Services\TemplateArchiveBuilder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class TemplateArchiveBuilderTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_template_archive_contains_only_generated_essential_files(): void
    {
        $loadout = Loadout::factory()->create([
            'title' => 'Agency Starter',
            'slug' => 'agency-starter',
        ]);
        LoadoutPackage::factory()->for($loadout)->create([
            'composer_name' => 'spatie/laravel-permission',
            'package_snapshot' => [
                'required_services' => ['redis'],
            ],
        ]);

        $path = app(TemplateArchiveBuilder::class)->build($loadout);
        $zip = new ZipArchive;

        $this->assertTrue($zip->open($path));

        $names = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);

            if (is_string($name)) {
                $names[] = $name;
            }
        }

        $this->assertSame([
            'composer.json',
            'package.json',
            'compose.yaml',
            '.env.loadout',
            'README.md',
        ], $names);
        $this->assertStringContainsString('spatie/laravel-permission', (string) $zip->getFromName('composer.json'));
        $this->assertFalse($zip->locateName('vendor/autoload.php') !== false);
        $this->assertFalse($zip->locateName('node_modules/.package-lock.json') !== false);

        $zip->close();
        File::delete($path);
    }

    public function test_builder_download_route_returns_zip_archive(): void
    {
        $this->withoutVite();

        $this->get(route('builder.review'))->assertOk();

        $this
            ->get(route('builder.download'))
            ->assertDownload();
    }
}
