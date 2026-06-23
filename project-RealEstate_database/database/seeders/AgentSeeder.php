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
        $agentUsers = User::query()->where('type', 'agent')->inRandomOrder()->get();
        $companies = Companies::query()->orderBy('id')->get();

        if ($agentUsers->isEmpty() || $companies->isEmpty()) {
            $this->command?->warn('AgentSeeder skipped: agent users or companies not found.');
            return;
        }

        $totalAgents = $agentUsers->count();
        $companyCount = $companies->count();

        // Distribute agents: 12-16 per company, total = $totalAgents
        $sizes = [];
        $remaining = $totalAgents;
        for ($i = 0; $i < $companyCount; $i++) {
            $isLast = $i === $companyCount - 1;
            $min = max(12, $remaining - ($companyCount - $i - 1) * 16);
            $max = min(16, $remaining - ($companyCount - $i - 1) * 12);
            $size = $isLast ? $remaining : fake()->numberBetween($min, $max);
            $sizes[] = $size;
            $remaining -= $size;
        }
        shuffle($sizes);

        $offset = 0;
        foreach ($companies as $ci => $company) {
            $count = $sizes[$ci];

            for ($j = 0; $j < $count; $j++) {
                $user = $agentUsers->get($offset + $j);
                if (!$user) break;

                Agent::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'companies_id' => $company->id,
                        'profile_image' => null,
                        'views' => fake()->numberBetween(5, 300),
                        'shares' => fake()->numberBetween(0, 50),
                        'trust_score' => 0,
                    ],
                );

                if ($j < 3) {
                    $user->socialLinks()->updateOrCreate(
                        ['platform' => fake()->randomElement(['linkedin', 'facebook', 'twitter'])],
                        ['url' => 'https://' . fake()->domainName()],
                    );
                }
            }

            $offset += $count;
        }
    }
}
