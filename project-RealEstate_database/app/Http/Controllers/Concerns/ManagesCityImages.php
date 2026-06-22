<?php
/*
|--------------------------------------------------------------------------
| ManagesCityImages — Trait لصور مدن (لوحة الإدارة)
|--------------------------------------------------------------------------
|
| الغرض:
| - استخراج بيانات المدينة من FormRequest مع رفع/استبدال صورة
| - حذف صورة المدينة من التخزين عند الحاجة
|
| الارتباطات:
| - يُستخدم عادة من كنترولر إدارة المدن (Admin)
| - Model: Cities
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Concerns;

use App\Models\Cities;
use App\Services\FileUploadService;
use Illuminate\Foundation\Http\FormRequest;

trait ManagesCityImages
{
    /**
     * تجهيز مصفوفة بيانات للإنشاء/التحديث مع معالجة ملف image.
     *
     * المدخلات:
     * - FormRequest (بعد التحقق safe())
     * - $existing: عند التحديث لحذف الصورة القديمة قبل الاستبدال
     *
     * المخرجات: array جاهزة لـ Cities::create/update
     *
     * الخطوات:
     * 1) أخذ كل الحقول ما عدا image من safe()
     * 2) إن وُجد ملف → حذف القديم (إن وجد) → storeImage في مجلد cities
     */
    protected function cityDataFromRequest(FormRequest $request, ?Cities $existing = null): array
    {
        $uploader = app(FileUploadService::class);
        $data = $request->safe()->except(['image']);

        $imageFile = $request->file('image');

        if ($imageFile && $imageFile->isValid()) {
            if ($existing) {
                $uploader->deleteIfExists($existing->image);
            }

            $data['image'] = $uploader->storeImage($imageFile, 'cities');
        }

        return $data;
    }

    /** حذف صورة المدينة من القرص عند حذف المدينة أو إزالة الصورة */
    protected function deleteCityImage(Cities $city): void
    {
        app(FileUploadService::class)->deleteIfExists($city->image);
    }
}
