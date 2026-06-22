<?php

namespace App\Http\Requests;

// =============================================================================
// Form Request: تحديث عقار موجود (حقول اختيارية جزئياً)
// =============================================================================
// يستخدم EstateValidationRules مع forUpdate: true → sometimes بدل required.

use App\Http\Requests\Concerns\EstateValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEstateRequest extends FormRequest
{
    use EstateValidationRules;

    /**
     * authorize: التحقق من ملكية العقار يتم في Controller/Policy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * rules: نفس حقول الإنشاء لكن بقواعد sometimes (تحديث جزئي).
     */
    public function rules(): array
    {
        return $this->estateFieldRules(forUpdate: true);
    }
}
