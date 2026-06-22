<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

trait WorkDaysValidationRules
{
    /**
     * Accept legacy string payloads and normalize to a weekday array.
     */
    protected function normalizeWorkDaysInput(): void
    {
        if (! $this->has('work_days')) {
            return;
        }

        $workDays = $this->input('work_days');

        if (! is_string($workDays)) {
            return;
        }

        $decoded = json_decode($workDays, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $workDays = $decoded;
        } elseif (str_contains($workDays, ',')) {
            $workDays = array_map('trim', explode(',', $workDays));
        } else {
            $workDays = [trim($workDays)];
        }

        $this->merge(['work_days' => $workDays]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function workDaysRules(bool $required = true): array
    {
        $presence = $required ? ['required'] : ['sometimes'];

        return [
            'work_days' => array_merge($presence, ['array', 'min:1']),
            'work_days.*' => ['required', 'string', Rule::in(config('realestate.work_days'))],
        ];
    }
}
