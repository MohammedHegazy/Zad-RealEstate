<?php
/*
|--------------------------------------------------------------------------
| ManagesEstateImages — Trait لمعرض صور العقار
|--------------------------------------------------------------------------
|
| Response formatting delegates to EstateImageService.
| Business logic lives in App\Services\EstateImageService.
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Concerns;

use App\Models\EstateImage;
use App\Services\EstateImageService;

trait ManagesEstateImages
{
    protected function formatEstateImage(EstateImage $image, ?EstateImageService $images = null): array
    {
        return ($images ?? app(EstateImageService::class))->formatForResponse($image);
    }
}
