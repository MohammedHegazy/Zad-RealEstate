<?php
/*
|--------------------------------------------------------------------------
| ManagesEstateVideos — Trait لإدارة فيديوهات العقار
|--------------------------------------------------------------------------
|
| الغرض:
| - رفع، تنسيق JSON، وحذف ملفات الفيديو من التخزين
| - يُشارك بين EstateVideoController و EstateController (عند الرفع الجماعي)
|
| الارتباطات:
| - Models: Estate, EstateVideo
| - Service: FileUploadService (مسارات public/storage)
| - جدول: estates_videos
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Concerns;

use App\Models\Estate;
use App\Models\EstateVideo;
use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;

trait ManagesEstateVideos
{
    /**
     * تحويل سجل فيديو إلى مصفوفة جاهزة للـ API مع رابط عام.
     *
     * المدخلات: EstateVideo + اختياري FileUploadService
     * المخرجات: array يحتوي حقول السجل + video_url
     */
    protected function formatEstateVideo(EstateVideo $video, ?FileUploadService $uploader = null): array
    {
        $uploader ??= app(FileUploadService::class);
        $data = $video->toArray();
        // publicUrl يحوّل المسار النسبي إلى URL يمكن للمتصفح فتحه
        $data['video_url'] = $uploader->publicUrl($video->video);

        return $data;
    }

    /**
     * رفع فيديو جديد وربطه بعقار.
     *
     * المدخلات: Estate + ملف UploadedFile
     * المخرجات: EstateVideo المُنشأ
     *
     * الخطوات:
     * 1) تخزين الملف تحت estates/{id}/videos
     * 2) إنشاء سجل في علاقة videos()
     */
    protected function createEstateVideo(Estate $estate, UploadedFile $file): EstateVideo
    {
        $uploader = app(FileUploadService::class);

        $path = $uploader->storeFile(
            $file,
            'estates/'.$estate->id.'/videos'
        );

        return $estate->videos()->create([
            'video' => $path,
        ]);
    }

    /**
     * حذف ملف الفيديو من القرص (قبل أو مع حذف السجل من DB).
     */
    protected function deleteEstateVideoFile(EstateVideo $video): void
    {
        app(FileUploadService::class)->deleteIfExists($video->video);
    }
}
