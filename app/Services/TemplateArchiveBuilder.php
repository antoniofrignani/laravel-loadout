<?php

namespace App\Services;

use App\Models\Loadout;
use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

class TemplateArchiveBuilder
{
    public function __construct(private ArtifactGenerator $generator) {}

    public function build(Loadout $loadout): string
    {
        $directory = storage_path('app/loadout-archives');

        File::ensureDirectoryExists($directory);

        $path = $directory.'/'.str($loadout->downloadFilename())->beforeLast('.zip')->toString().'-'.str()->random(8).'.zip';
        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create loadout archive.');
        }

        foreach ($this->generator->preview($loadout) as $artifact) {
            $zip->addFromString($artifact['name'], $artifact['content']);
        }

        $zip->close();

        return $path;
    }
}
