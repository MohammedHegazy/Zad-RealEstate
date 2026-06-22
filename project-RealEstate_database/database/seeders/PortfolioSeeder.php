<?php

namespace Database\Seeders;

use App\Enums\InvestmentGoal;
use App\Models\Cities;
use App\Models\Estate;
use App\Models\InvestmentPortfolio;
use App\Models\Places;
use App\Models\PortfolioProperty;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $buyer = User::query()->where('email', 'buyer@realestate.test')->first();

        if (! $buyer) {
            $this->command?->warn('PortfolioSeeder skipped: demo buyer not found.');

            return;
        }

        $damascus = Cities::query()->where('name', 'دمشق')->first();
        $malki = Places::query()->where('name', 'المالكي')->first();

        UserPreference::query()->updateOrCreate(
            ['user_id' => $buyer->id],
            [
                'cities_id' => $damascus?->id,
                'places_id' => $malki?->id,
                'min_budget' => 300_000,
                'max_budget' => 900_000,
                'preferred_property_type' => 'apartment',
                'preferred_rooms' => 3,
                'property_function' => 'invest',
                'investment_goal' => InvestmentGoal::RentalIncome,
                'risk_level' => 'moderate',
                'interests' => ['high_roi', 'near_services'],
            ],
        );

        $portfolio = InvestmentPortfolio::query()->updateOrCreate(
            [
                'user_id' => $buyer->id,
                'is_default' => true,
            ],
            [
                'name' => config('realestate.default_portfolio_name', 'My Portfolio'),
                'description' => 'Default watchlist for rental-focused investments.',
                'target_budget' => 750_000,
                'risk_level' => 'moderate',
                'status' => InvestmentPortfolio::STATUS_ACTIVE,
            ],
        );

        $estate = Estate::query()->where('name', 'like', 'شقة المزة%')->first();

        if ($estate) {
            PortfolioProperty::query()->updateOrCreate(
                [
                    'portfolio_id' => $portfolio->id,
                    'estate_id' => $estate->id,
                ],
                [
                    'status' => PortfolioProperty::STATUS_TRACKING,
                    'investment_amount' => null,
                    'notes' => 'Monitoring rental yield before making an offer.',
                    'invested_at' => null,
                    'sold_at' => null,
                ],
            );
        }
    }
}
