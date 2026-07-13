<?php

namespace App\Http\Controllers\Builder;

use App\Http\Controllers\Controller;
use App\Services\LoadoutBuilder;
use App\Services\TemplateArchiveBuilder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    public function __invoke(Request $request, LoadoutBuilder $builder, TemplateArchiveBuilder $archives): BinaryFileResponse
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());
        $path = $archives->build($loadout);

        return response()
            ->download($path, 'laravel-loadout-'.$loadout->slug.'.zip')
            ->deleteFileAfterSend();
    }
}
