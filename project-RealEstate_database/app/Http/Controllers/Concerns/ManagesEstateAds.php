<?php
/*
|--------------------------------------------------------------------------
| ManagesEstateAds — Trait لصور الإعلان الترويجية للعقار
|--------------------------------------------------------------------------
|
| الغرض:
| - إدارة صور إضافية للتسويق (ليست بالضرورة معرض العقار العادي)
| - حقل is_main يحدد الصورة البارزة في البطاقة
|
| الارتباطات:
| - EstateAdController
| - جدول: estate_ads
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Concerns;

use App\Models\Estate;
use App\Models\EstateAd;
use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;

trait ManagesEstateAds
{
    /** تنسيق سجل إعلان مع image_url */
    protected function formatEstateAd(EstateAd $ad, ?FileUploadService $uploader = null): array
    {
        $uploader ??= app(FileUploadService::class);
        $data = $ad->toArray();
        $data['image_url'] = $uploader->publicUrl($ad->image);

        return $data;
    }

    /**
     * رفع صورة إعلان؛ إن is_main=true تُزال الرئيسية عن بقية الإعلانات.
     *
     * مسار التخزين: estates/{id}/ads
     */
    protected function createEstateAd(Estate $estate, UploadedFile $file, bool $isMain): EstateAd
    {
        $uploader = app(FileUploadService::class);

        $path = $uploader->storeImage(
            $file,
            'estates/'.$estate->id.'/ads'
        );

        if ($isMain) {
            $estate->ads()->update(['is_main' => false]);
        }

        return $estate->ads()->create([
            'image' => $path,
            'is_main' => $isMain,
        ]);
    }

    protected function deleteEstateAdFile(EstateAd $ad): void
    {
        app(FileUploadService::class)->deleteIfExists($ad->image);
    }
}
