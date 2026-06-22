<?php

namespace App\Http\Requests;

// =============================================================================
// Form Request: إنشاء عقار جديد — يدمج EstateValidationRules
// =============================================================================
// Form Request يُحقن في Controller؛ Laravel يشغّل التحقق قبل تنفيذ الدالة.

use App\Http\Requests\Concerns\EstateValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreEstateRequest extends FormRequest
{
    use EstateValidationRules;

    /**
     * authorize: إنشاء عقار — الصلاحية الفعلية تُفحص في Controller/Middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * rules للإنشاء: كل الحقول المطلوبة عبر estateFieldRules() بدون forUpdate.
     */
    public function rules(): array
    {
        return $this->estateFieldRules();
    }
}
