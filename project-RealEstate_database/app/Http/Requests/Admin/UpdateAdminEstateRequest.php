<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\EstateValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminEstateRequest extends FormRequest
{
    use EstateValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->estateFieldRules(forUpdate: true), [
            'status' => ['sometimes', Rule::in(config('realestate.estate_statuses'))],
            'user_id' => ['sometimes', 'exists:users,id'],
        ]);
    }
}
