<?php

namespace App\Http\Requests\Auth;

// =============================================================================
// Form Request: تحديث ملف المستخدم الشخصي (المسجل دخوله)
// =============================================================================
// Form Request = طبقة تحقق تُنفَّذ قبل Controller. فشل التحقق → 422 JSON.
// sometimes = الحقل اختياري؛ يُتحقق منه فقط إن وُجد في الطلب (تحديث جزئي).

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * authorize: true — يفترض أن route محمي بـ auth:sanctum.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * rules: تحديث جزئي؛ unique يتجاهل سجل المستخدم الحالي.
     */
    public function rules(): array
    {
        // معرف المستخدم الحالي لاستثنائه من قواعد unique
        $userId = $this->user()->id;

        return [
            'username' => ['sometimes', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'fname' => ['sometimes', 'string', 'max:255'],
            'lname' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['sometimes', 'string', 'max:50'],
            'countre_code_phone' => ['sometimes', 'string', 'max:10'],
            'gender' => ['sometimes', 'string', 'max:20'],
            'facebook' => ['nullable', 'string', 'max:500'],
            'instagram' => ['nullable', 'string', 'max:500'],
        ];
    }
}
