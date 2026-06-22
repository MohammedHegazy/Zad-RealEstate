<?php

namespace App\Http\Requests;

use App\Models\PortfolioItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePortfolioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'estate_id' => ['required', 'integer', 'exists:estates,id'],
            'portfolio_id' => ['sometimes', 'integer', 'exists:portfolios,id'],
            'status' => ['sometimes', 'string', Rule::in([
                PortfolioItem::STATUS_TRACKING,
                PortfolioItem::STATUS_INVESTED,
            ])],
            'investment_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
