<?php

namespace App\Http\Requests\Auth;

// =============================================================================
// Form Request: التحقق من بيانات تسجيل الدخول قبل وصولها إلى AuthController
// =============================================================================
// فشل التحقق = استجابة 422 JSON تلقائياً دون استدعاء Controller.

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * authorize: هل المستخدم مسموح له بإرسال هذا الطلب؟
     * true = مسار عام (لا يحتاج صلاحية مسبقة).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * rules: قواعد التحقق — عند الفشل Laravel يرجع 422 تلقائياً.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
