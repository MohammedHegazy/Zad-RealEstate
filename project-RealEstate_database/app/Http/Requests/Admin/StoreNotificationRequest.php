<?php

namespace App\Http\Requests\Admin;

// =============================================================================
// Form Request (Admin): إنشاء إشعار لمستخدم
// =============================================================================

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'content' => ['required', 'string'],
            'is_read' => ['nullable', 'boolean'],
        ];
    }
}
