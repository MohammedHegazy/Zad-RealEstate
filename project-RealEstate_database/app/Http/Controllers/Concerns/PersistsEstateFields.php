<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Estate;
use App\Services\EstateImageService;
use App\Services\FileUploadService;
use App\Services\Investment\InvestmentCalculatorService;
use Illuminate\Http\Request;

trait PersistsEstateFields
{
    /**
     * @return array<string, mixed>
     */
    protected function prepareEstatePayload(Request $request, bool $includeSocial = false): array
    {
        $except = ['images', 'videos', 'ads', 'primary_image_index', 'main_ad_index'];

        if ($includeSocial) {
            $except[] = 'facebook';
            $except[] = 'instagram';
        } else {
            $except = array_merge($except, ['facebook', 'instagram']);
        }

        return $request->safe()->except($except);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function applyEstateInvestmentMetrics(
        array &$data,
        ?Estate $estate = null,
        ?InvestmentCalculatorService $calculator = null,
    ): void {
        ($calculator ?? app(InvestmentCalculatorService::class))
            ->applyToEstatePayload($data, $estate);
    }

    protected function syncEstateUploadedMedia(
        Request $request,
        Estate $estate,
        FileUploadService $uploader,
        EstateImageService $estateImages,
    ): void {
        if ($request->hasFile('images')) {
            $primaryIndex = $request->integer('primary_image_index', 0);

            foreach ($request->file('images') as $index => $file) {
                $estateImages->createImage(
                    $estate,
                    $file,
                    $index === $primaryIndex,
                );
            }
        }

        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $file) {
                $path = $uploader->storeFile($file, 'estates/'.$estate->id.'/videos');
                $estate->videos()->create(['video' => $path]);
            }
        }

        if ($request->hasFile('ads')) {
            $mainIndex = $request->integer('main_ad_index', 0);

            foreach ($request->file('ads') as $index => $file) {
                $path = $uploader->storeImage($file, 'estates/'.$estate->id.'/ads');
                $isMain = $index === $mainIndex;

                if ($isMain) {
                    $estate->ads()->update(['is_main' => false]);
                }

                $estate->ads()->create([
                    'image' => $path,
                    'is_main' => $isMain,
                ]);
            }
        }
    }
}
