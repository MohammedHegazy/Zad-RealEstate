<?php

namespace App\Http\Requests\Concerns;

// =============================================================================
// Trait: قواعد تحقق مشتركة لإنشاء/تحديث العقار — يُعاد استخدامها في عدة Requests
// =============================================================================
// Trait = مجموعة دوال تُدمج في Form Request عبر use — لتجنب تكرار rules.

trait EstateValidationRules
{
    /**
     * قواعد حقول العقار.
     *
     * @param bool $forUpdate إن true تستخدم sometimes بدل required (تحديث جزئي)
     * @return array قواعد Laravel Validation
     */
    protected function estateFieldRules(bool $forUpdate = false): array
    {
        $required = $forUpdate ? 'sometimes' : 'required';

        return array_merge([
            'places_id' => [$required, 'exists:places,id'],
        ], $this->estateCoordinateRules($forUpdate), [
            'name' => [$required, 'string', 'max:255'],
            'phone' => [$required, 'string', 'max:50'],
            'country_code_phone' => [$required, 'string', 'max:10'],
            'space_of_estate' => [$required, 'numeric', 'min:0'],
            'price_of_meter' => [$required, 'numeric', 'min:0'],
            'price' => [$required, 'numeric', 'min:0'],
            'monthly_rent' => ['nullable', 'numeric', 'min:0'],
            'annual_expenses' => ['nullable', 'numeric', 'min:0'],
            'maintenance_cost' => ['nullable', 'numeric', 'min:0'],
            'annual_property_tax' => ['nullable', 'numeric', 'min:0'],
            'annual_hoa_or_service' => ['nullable', 'numeric', 'min:0'],
            'occupancy_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'floor' => ['nullable', 'integer', 'min:0'],
            'num_of_bedrooms' => ['nullable', 'integer', 'min:0'],
            'num_of_livingrooms' => ['nullable', 'integer', 'min:0'],
            'num_of_receptions' => ['nullable', 'integer', 'min:0'],
            'num_of_bathrooms' => ['nullable', 'integer', 'min:0'],
            'num_of_kitchens' => ['nullable', 'integer', 'min:0'],
            'num_of_balconies' => ['nullable', 'integer', 'min:0'],
            'type_text' => [$required, 'string', 'max:255'],
            'kind_text' => [$required, 'string', 'max:255'],
            'is_furnished' => ['nullable', 'boolean'],
            'description' => [$required, 'string'],
            'real_number' => ['nullable', 'string', 'max:255'],
            'date_of_build' => ['nullable', 'string', 'max:50'],
            'state_of_build' => ['nullable', 'string', 'max:255'],
            'rent_kind' => ['nullable', 'string', 'max:255'],
            'rent_description' => ['nullable', 'string'],
            'facebook' => ['nullable', 'string', 'max:500'],
            'instagram' => ['nullable', 'string', 'max:500'],
            'links' => ['nullable', 'array'],
            'links.*.platform' => ['required_with:links', 'string', 'max:50'],
            'links.*.url' => ['required_with:links', 'string', 'max:500'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['image', 'max:'.config('realestate.upload.max_image_kb')],
            'primary_image_index' => ['nullable', 'integer', 'min:0'],
            'videos' => ['nullable', 'array', 'max:5'],
            'videos.*' => ['file', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm', 'max:'.config('realestate.upload.max_video_kb')],
            'ads' => ['nullable', 'array', 'max:10'],
            'ads.*' => ['image', 'max:'.config('realestate.upload.max_image_kb')],
            'main_ad_index' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    /**
     * إحداثيات WGS84 لـ Leaflet / OpenStreetMap.
     */
    protected function estateCoordinateRules(bool $forUpdate = false): array
    {
        $required = $forUpdate ? 'sometimes' : 'required';

        return [
            'latitude' => [$required, 'numeric', 'between:-90,90'],
            'longitude' => [$required, 'numeric', 'between:-180,180'],
        ];
    }
}
