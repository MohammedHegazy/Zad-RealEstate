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
        $buyer = User::query()->where('email', 'buyer@realestate.test')->first();
        $company = Companies::query()->where('company_name', 'Acme Realty')->first();
        $agent = Agent::query()->whereHas('user', fn ($q) => $q->where('email', 'agent@realestate.test'))->first();
        $estate = Estate::query()->where('name', 'Sunlit Mazzeh Apartment')->first();

        if (! $buyer) {
            $this->command?->warn('ReviewSeeder skipped: demo buyer not found.');

            return;
        }

        if ($estate) {
            PropertyReview::query()->updateOrCreate(
                ['user_id' => $buyer->id, 'estate_id' => $estate->id],
                [
                    'rating' => 5,
                    'review' => 'Well maintained property in a convenient location.',
                    'status' => 'approved',
                ],
            );
        }

        if ($agent) {
            AgentReview::query()->updateOrCreate(
                ['user_id' => $buyer->id, 'agent_id' => $agent->id],
                [
                    'rating' => 4,
                    'review' => 'Responsive agent with clear communication.',
                    'status' => 'approved',
                ],
            );

            app(TrustScoreService::class)->recalculateForAgent($agent);
        }

        if ($company) {
            CompanyReview::query()->updateOrCreate(
                ['user_id' => $buyer->id, 'company_id' => $company->id],
                [
                    'rating' => 5,
                    'review' => 'Professional team and smooth transaction process.',
                    'status' => 'approved',
                ],
            );

            app(TrustScoreService::class)->recalculateForCompany($company);
        }
    }
}
