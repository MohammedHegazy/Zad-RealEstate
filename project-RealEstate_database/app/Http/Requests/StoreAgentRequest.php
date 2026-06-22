<?php

namespace App\Http\Requests;

// =============================================================================
// Form Request: تسجيل مستخدم كوكيل عقاري
// =============================================================================
// Form Request = تحقق تلقائي قبل Controller. unique على agents.user_id يمنع التكرار.

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAgentRequest extends FormRequest
{
    /** authorize: مسموح — الصلاحية في middleware. */
    public function authorize(): bool
    {
        return true;
    }

    /** rules: user_id موجود وغير مكرر كوكيل؛ صورة اختيارية. */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('agents', 'user_id'),
            ],
            'profile_image' => ['nullable', 'image', 'max:'.config('realestate.upload.max_image_kb')],
        ];
    }

    /** messages: رسائل خطأ مخصصة بالإنجليزية (يمكن تعريبها لاحقاً). */
    public function messages(): array
    {
        return [
            'user_id.unique' => 'This user is already registered as an agent.',
        ];
    }
}
