<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\AgentReview;
use App\Models\Companies;
use App\Models\CompanyReview;
use App\Models\Estate;
use App\Models\PropertyReview;
use App\Models\User;
use App\Services\Trust\TrustScoreService;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $buyers = User::query()->where('type', 'buyer')->get();
        $companies = Companies::query()->with('agents')->get();
        $agents = Agent::query()->with('company')->get();
        $estates = Estate::query()->inRandomOrder()->take(50)->get();

        if ($buyers->isEmpty()) {
            $this->command?->warn('ReviewSeeder skipped: no buyers found.');
            return;
        }

        $buyerIds = $buyers->pluck('id');

        $reviewTexts = [
            'Excellent service and very professional team. Highly recommended.',
            'Great experience working with them. Very responsive and helpful.',
            'Good communication and smooth transaction process.',
            'Professional and trustworthy. Would work with them again.',
            'Very knowledgeable about the local market. Great advice.',
            'Satisfied with the service provided. Clear and transparent.',
            'Reliable and efficient. Made the whole process easy.',
            'Above and beyond expectations. Truly dedicated professionals.',
            'One of the best in the business. Fair prices and excellent follow-up.',
            'Quick response time and very helpful throughout the entire process.',
            'Highly professional approach to real estate. Very satisfied.',
            'Great attention to detail and customer service.',
            'Trustworthy and transparent in all dealings.',
            'Made finding the right property very easy and stress-free.',
            'Exceptional market knowledge and negotiation skills.',
            'Very accommodating and always available when needed.',
            'Smooth transaction from start to finish. Highly recommended.',
            'Professional approach with great results.',
            'Would definitely use their services again in the future.',
            'Outstanding service quality and customer care.',
        ];

        $trustService = app(TrustScoreService::class);

        $this->command?->info('Seeding company reviews...');

        foreach ($companies as $company) {
            $count = fake()->numberBetween(10, 20);
            $selected = $buyerIds->random(min($count, $buyerIds->count()));

            foreach ($selected as $buyerId) {
                CompanyReview::firstOrCreate(
                    ['user_id' => $buyerId, 'company_id' => $company->id],
                    [
                        'rating' => fake()->numberBetween(3, 5),
                        'review' => fake()->randomElement($reviewTexts),
                        'status' => 'approved',
                    ],
                );
            }

            $trustService->recalculateForCompany($company);
        }

        $this->command?->info('Seeding agent reviews...');

        foreach ($agents as $agent) {
            $count = fake()->numberBetween(10, 20);
            $selected = $buyerIds->random(min($count, $buyerIds->count()));

            foreach ($selected as $buyerId) {
                AgentReview::firstOrCreate(
                    ['user_id' => $buyerId, 'agent_id' => $agent->id],
                    [
                        'rating' => fake()->numberBetween(3, 5),
                        'review' => fake()->randomElement($reviewTexts),
                        'status' => 'approved',
                    ],
                );
            }

            $trustService->recalculateForAgent($agent);
        }

        $this->command?->info('Seeding estate reviews...');

        foreach ($estates as $estate) {
            $count = fake()->numberBetween(1, 3);
            $selected = $buyerIds->random(min($count, $buyerIds->count()));

            foreach ($selected as $buyerId) {
                PropertyReview::firstOrCreate(
                    ['user_id' => $buyerId, 'estate_id' => $estate->id],
                    [
                        'rating' => fake()->numberBetween(3, 5),
                        'review' => fake()->randomElement($reviewTexts),
                        'status' => 'approved',
                    ],
                );
            }
        }
    }
}
