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
        $agentUsers = User::query()->where('type', 'agent')->orderBy('id')->get();
        $companies = Companies::query()->orderBy('id')->get();

        if ($agentUsers->isEmpty() || $companies->isEmpty()) {
            $this->command?->warn('AgentSeeder skipped: agent users or companies not found.');
            return;
        }

        $perCompany = $companies->count();

        foreach ($agentUsers as $i => $user) {
            $company = $companies->get($i % $perCompany);

            Agent::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'companies_id' => $company->id,
                    'profile_image' => null,
                    'views' => fake()->numberBetween(5, 200),
                    'shares' => fake()->numberBetween(0, 30),
                    'trust_score' => 0,
                ],
            );

            // Add social link for each agent
            if ($i === 0) {
                $user->socialLinks()->updateOrCreate(
                    ['platform' => 'linkedin'],
                    ['url' => 'https://linkedin.com/in/rami-saleh-agent'],
                );
            }
        }
    }
}
