<?php

namespace App\Http\Requests\Auth;

// =============================================================================
// Form Request: التحقق من بيانات التسجيل — يمنع إنشاء حساب admin من API العام
// =============================================================================
// type يُقيد بـ user_types ويُستبعد admin_types من config/realestate.

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
{
    /**
     * authorize: مسار التسجيل عام — لا يحتاج تسجيل دخول مسبق.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * rules: حقول إنشاء حساب جديد؛ type ممنوع أن يكون admin (من config).
     */
    public function rules(): array
    {
        // أنواع المستخدمين المحظورة من التسجيل الذاتي (مدير)
        $forbiddenTypes = config('realestate.admin_types', ['admin']);

        return [
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'fname' => ['required', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'max:50'],
            'countre_code_phone' => ['required', 'string', 'max:10'],
            'gender' => ['required', 'string', 'max:20'],
            // type: يجب أن يكون من user_types وليس من admin_types
            'type' => ['required', 'string', Rule::in(config('realestate.user_types')), Rule::notIn($forbiddenTypes)],
            'facebook' => ['nullable', 'string', 'max:500'],
            'instagram' => ['nullable', 'string', 'max:500'],
        ];
    }
}
