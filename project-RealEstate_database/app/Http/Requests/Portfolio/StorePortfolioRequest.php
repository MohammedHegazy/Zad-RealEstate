<?php

namespace App\Http\Requests\Portfolio;

use App\Models\InvestmentPortfolio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'target_budget' => ['nullable', 'numeric', 'min:0'],
            'risk_level' => ['nullable', 'string', Rule::in(InvestmentPortfolio::riskLevels())],
            'status' => ['nullable', 'string', Rule::in(InvestmentPortfolio::statuses())],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
