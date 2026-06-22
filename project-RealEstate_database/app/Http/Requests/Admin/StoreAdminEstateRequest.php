<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\EstateValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminEstateRequest extends FormRequest
{
    use EstateValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->estateFieldRules(), [
            'user_id' => ['required', 'exists:users,id'],
            'status' => ['nullable', Rule::in(config('realestate.estate_statuses'))],
        ]);
    }
}
