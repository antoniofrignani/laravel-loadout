<?php

namespace App\Http\Controllers;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Services\TemplateArchiveBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LoadoutDownloadController extends Controller
{
    public function __invoke(Loadout $loadout, TemplateArchiveBuilder $archives): BinaryFileResponse
    {
        abort_if($loadout->visibility === LoadoutVisibility::Private, 404);

        $path = $archives->build($loadout);

        return response()
            ->download($path, $loadout->downloadFilename())
            ->deleteFileAfterSend();
    }
}
