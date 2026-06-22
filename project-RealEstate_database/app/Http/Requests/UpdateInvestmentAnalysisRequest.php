<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvestmentAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'monthly_rent' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'annual_expenses' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'maintenance_cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'tax_cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'occupancy_rate' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
