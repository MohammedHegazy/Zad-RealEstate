<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Companies;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agent>
 */
class AgentFactory extends Factory
{
    protected $model = Agent::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'companies_id' => Companies::factory(),
            'profile_image' => null,
            'views' => 0,
            'shares' => 0,
            'trust_score' => 0,
        ];
    }

    public function forCompany(Companies $company): static
    {
        return $this->state(fn () => ['companies_id' => $company->id]);
    }
}
