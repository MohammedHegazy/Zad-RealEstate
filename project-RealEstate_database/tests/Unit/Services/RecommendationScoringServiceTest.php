<?php

namespace Tests\Unit\Services;

use App\Enums\InvestmentGoal;
use App\Models\Cities;
use App\Models\Estate;
use App\Models\Places;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\RecommendationScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    private RecommendationScoringService $scoring;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scoring = app(RecommendationScoringService::class);
    }

    public function test_scores_estate_within_budget_and_location(): void
    {
        $city = Cities::factory()->create();
        $place = Places::factory()->create(['cities_id' => $city->id]);
        $owner = User::factory()->create();

        $preference = UserPreference::factory()->create([
            'user_id' => User::factory(),
            'cities_id' => $city->id,
            'places_id' => $place->id,
            'min_budget' => 200_000,
            'max_budget' => 400_000,
            'preferred_property_type' => 'apartment',
            'investment_goal' => InvestmentGoal::RentalIncome,
            'risk_level' => 'moderate',
        ]);

        $estate = Estate::factory()->forOwner($owner)->create([
            'places_id' => $place->id,
            'price' => 350_000,
            'type_text' => 'apartment',
            'kind_text' => 'sale',
            'roi' => 8.5,
            'monthly_rent' => 2500,
            'status' => 'active',
        ]);

        $result = $this->scoring->scoreEstate($estate, $preference);

        $this->assertGreaterThanOrEqual(70, $result['score']);
        $this->assertGreaterThanOrEqual(70, $result['matching_percentage']);
        $this->assertArrayHasKey('budget_match', $result['factors']);
        $this->assertArrayHasKey('location_match', $result['factors']);
        $this->assertNotEmpty($result['reasons']);
    }

    public function test_score_is_capped_at_100(): void
    {
        $city = Cities::factory()->create();
        $place = Places::factory()->create(['cities_id' => $city->id]);
        $owner = User::factory()->create();

        $preference = UserPreference::factory()->create([
            'cities_id' => $city->id,
            'places_id' => $place->id,
            'min_budget' => 100_000,
            'max_budget' => 1_000_000,
            'preferred_property_type' => 'villa',
            'investment_goal' => InvestmentGoal::RentalIncome,
            'risk_level' => 'high',
        ]);

        $estate = Estate::factory()->forOwner($owner)->create([
            'places_id' => $place->id,
            'price' => 500_000,
            'type_text' => 'villa',
            'kind_text' => 'sale',
            'roi' => 15,
            'monthly_rent' => 5000,
            'status' => 'active',
        ]);

        $result = $this->scoring->scoreEstate($estate, $preference);

        $this->assertLessThanOrEqual(100, $result['score']);
    }

    public function test_similarity_score_favors_same_area_and_type(): void
    {
        $city = Cities::factory()->create();
        $place = Places::factory()->create(['cities_id' => $city->id]);
        $owner = User::factory()->create();

        $source = Estate::factory()->forOwner($owner)->create([
            'places_id' => $place->id,
            'price' => 300_000,
            'type_text' => 'apartment',
            'num_of_bedrooms' => 3,
            'status' => 'active',
        ]);

        $similar = Estate::factory()->forOwner($owner)->create([
            'places_id' => $place->id,
            'price' => 310_000,
            'type_text' => 'apartment',
            'num_of_bedrooms' => 3,
            'status' => 'active',
        ]);

        $different = Estate::factory()->forOwner($owner)->create([
            'places_id' => Places::factory()->create(['cities_id' => $city->id])->id,
            'price' => 900_000,
            'type_text' => 'land',
            'num_of_bedrooms' => 0,
            'status' => 'active',
        ]);

        $similarScore = $this->scoring->scoreSimilarity($source, $similar);
        $differentScore = $this->scoring->scoreSimilarity($source, $different);

        $this->assertGreaterThan($differentScore, $similarScore);
    }
}
