<?php
/*
|--------------------------------------------------------------------------
| FormatsCompanyResponse — تنسيق الشركة في رد API
|--------------------------------------------------------------------------
| يحوّل مسارات profile_image و banner_image إلى روابط عامة للمتصفح/التطبيق.
|--------------------------------------------------------------------------
*/

namespace App\Traits;

use App\Models\Companies;
use App\Services\FileUploadService;

trait FormatsCompanyResponse
{
    protected function formatCompany(Companies $company, ?FileUploadService $uploader = null): array
    {
        $uploader ??= app(FileUploadService::class);

        $data = $company->toArray();
        $data['profile_image_url'] = $uploader->publicUrl($company->profile_image);
        $data['banner_image_url'] = $uploader->publicUrl($company->banner_image);

        return $data;
    }
}
