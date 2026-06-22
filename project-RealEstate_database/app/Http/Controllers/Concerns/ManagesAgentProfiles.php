<?php
/*
|--------------------------------------------------------------------------
| ManagesAgentProfiles — Trait لصور وملفات الوسطاء
|--------------------------------------------------------------------------
|
| الغرض:
| - معالجة رفع صورة profile_image للوسيط
| - حذف الصورة عند إزالة الوسيط
|
| الارتباطات:
| - AgentController (تحديث الملف الشخصي وإدارة الشركة)
| - Model: Agent
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Concerns;

use App\Models\Agent;
use App\Services\FileUploadService;
use Illuminate\Foundation\Http\FormRequest;

trait ManagesAgentProfiles
{
    /**
     * استخراج بيانات الوسيط من الطلب مع رفع صورة اختيارية.
     *
     * المخرجات: مصفوفة لـ update/create على جدول agents
     */
    protected function agentDataFromRequest(FormRequest $request, ?Agent $existing = null): array
    {
        $uploader = app(FileUploadService::class);
        $data = $request->safe()->except(['profile_image']);

        $profileImage = $request->file('profile_image');

        if ($profileImage && $profileImage->isValid()) {
            if ($existing) {
                $uploader->deleteIfExists($existing->profile_image);
            }

            $data['profile_image'] = $uploader->storeImage(
                $profileImage,
                'agents/profile'
            );
        }

        return $data;
    }

    /** حذف صورة الوسيط من التخزين قبل حذف السجل */
    protected function deleteAgentProfileImage(Agent $agent): void
    {
        app(FileUploadService::class)->deleteIfExists($agent->profile_image);
    }
}
