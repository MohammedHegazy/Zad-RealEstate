<?php

namespace App\Http\Requests\Portfolio;

use App\Models\PortfolioItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePortfolioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'nullable', 'string', Rule::in(PortfolioItem::statuses())],
            'investment_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('status')) {
                return;
            }

            if ($this->has('investment_amount') || $this->has('notes')) {
                return;
            }

            $validator->errors()->add(
                'status',
                'Provide status, investment_amount, or notes to update this portfolio item.'
            );
        });
    }
}
