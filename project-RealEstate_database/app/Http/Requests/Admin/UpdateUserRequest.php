<?php

namespace App\Http\Requests\Admin;

// =============================================================================
// Form Request (Admin): تحديث مستخدم
// =============================================================================

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // معرف المستخدم من route لقواعد unique
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'username' => ['sometimes', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'fname' => ['sometimes', 'string', 'max:255'],
            'lname' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['sometimes', 'string', 'max:50'],
            'country_code_phone' => ['sometimes', 'string', 'max:10'],
            'gender' => ['sometimes', 'string', 'max:20'],
            'type' => ['sometimes', 'string', Rule::in(config('realestate.user_types'))],
            'status' => ['sometimes', 'string', Rule::in(config('realestate.user_statuses'))],
            'is_verified' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
