<?php
/*
|--------------------------------------------------------------------------
| FormatsAgentResponse — تنسيق رد API للوسيط العقاري
|--------------------------------------------------------------------------
| Trait = مجموعة دوال تُنسخ إلى Controller عبر use FormatsAgentResponse;
|
| لماذا Trait وليس Service؟
| - التنسيق مرتبط بشكل JSON للرد — يُعاد استخدامه في Admin و API العام.
| - يبقى Controller رفيعاً: formatAgent($agent) بدل تكرار المصفوفات.
|--------------------------------------------------------------------------
*/

namespace App\Traits;

use App\Models\Agent;
use App\Models\Companies;
use App\Models\User;
use App\Services\FileUploadService;

trait FormatsAgentResponse
{
    /**
     * تحويل Model Agent إلى مصفوفة جاهزة للـ JSON.
     *
     * - toArray(): كل أعمدة الجدول
     * - profile_image_url: رابط عام للصورة (ليس مساراً نسبياً فقط)
     * - relationLoaded: نضيف user/company فقط إن تم تحميلهما مسبقاً (with/load)
     * - approved_reviews_avg_rating / approved_reviews_count: from loadAvg/loadCount on approved reviews
     */
    protected function formatAgent(Agent $agent, ?FileUploadService $uploader = null): array
    {
        // app() = حل الخدمة من حاوية Laravel إن لم تُمرَّر يدوياً
        $uploader ??= app(FileUploadService::class);

        $data = $agent->toArray();
        $data['profile_image_url'] = $uploader->publicUrl($agent->profile_image);

        if ($agent->relationLoaded('user') && $agent->user) {
            $data['user'] = $this->formatAgentUser($agent->user);
        }

        if ($agent->relationLoaded('company') && $agent->company) {
            $data['company'] = $this->formatAgentCompany($agent->company);
        }

        if (isset($agent->approved_reviews_avg_rating)) {
            $data['average_rating'] = $agent->approved_reviews_avg_rating !== null
                ? round((float) $agent->approved_reviews_avg_rating, 1)
                : null;
        }

        if (isset($agent->approved_reviews_count)) {
            $data['ratings_count'] = $agent->approved_reviews_count;
        }

        return $data;
    }

    /** حقول المستخدم الآمنة للعرض — only يستبعد password و tokens */
    protected function formatAgentUser(User $user): array
    {
        return $user->only(['id', 'username', 'fname', 'lname', 'email', 'phone', 'type']);
    }

    /** ملخص الشركة المرتبطة بالوسيط */
    protected function formatAgentCompany(Companies $company): array
    {
        return $company->only(['id', 'company_name', 'places_id']);
    }
}
