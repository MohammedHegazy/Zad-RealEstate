<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Companies;
use App\Models\User;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        $agentUser = User::query()->where('email', 'agent@realestate.test')->first();
        $company = Companies::query()->where('company_name', 'Acme Realty')->first();

        if (! $agentUser || ! $company) {
            $this->command?->warn('AgentSeeder skipped: demo agent user or company not found.');

            return;
        }

        Agent::query()->updateOrCreate(
            ['user_id' => $agentUser->id],
            [
                'companies_id' => $company->id,
                'profile_image' => null,
                'views' => 24,
                'shares' => 3,
                'trust_score' => 0,
            ],
        );

        $agentUser->socialLinks()->updateOrCreate(
            ['platform' => 'linkedin'],
            ['url' => 'https://linkedin.com/in/rami-saleh-agent'],
        );
    }
}
