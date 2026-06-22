<?php

namespace Tests\Feature\Recommendations;

use App\Enums\InvestmentGoal;
use App\Models\Cities;
use App\Models\Estate;
use App\Models\Places;
use App\Models\Favorit_estate;
use App\Models\Recommendation;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRecommendationApi;
use Tests\TestCase;

class RecommendationTest extends TestCase
{
    use InteractsWithRecommendationApi;
    use RefreshDatabase;

    public function test_user_gets_recommendations_after_saving_preferences(): void
    {
        $user = User::factory()->create();
        $city = Cities::factory()->create();
        $place = Places::factory()->create(['cities_id' => $city->id]);
        $owner = User::factory()->create();

        UserPreference::factory()->forUser($user)->create([
            'cities_id' => $city->id,
            'places_id' => $place->id,
            'min_budget' => 200_000,
            'max_budget' => 500_000,
            'preferred_property_type' => 'apartment',
            'investment_goal' => InvestmentGoal::RentalIncome,
            'risk_level' => 'moderate',
        ]);

        Estate::factory()->forOwner($owner)->count(3)->create([
            'places_id' => $place->id,
            'price' => 350_000,
            'type_text' => 'apartment',
            'kind_text' => 'sale',
            'roi' => 7.5,
            'monthly_rent' => 2000,
            'status' => 'active',
        ]);

        $response = $this->actingAsApiUser($user)
            ->getJson($this->apiPrefix().'/recommendations?refresh=1');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'recommendations' => [
                    '*' => [
                        'recommendation_score',
                        'matching_percentage',
                        'why_recommended',
                        'score_factors',
                    ],
                ],
            ],
        ]);

        $this->assertGreaterThan(0, Recommendation::query()->where('user_id', $user->id)->count());
    }

    public function test_top_recommendations_returns_limited_results(): void
    {
        $user = User::factory()->create();
        $city = Cities::factory()->create();
        $place = Places::factory()->create(['cities_id' => $city->id]);
        $owner = User::factory()->create();

        UserPreference::factory()->forUser($user)->create([
            'cities_id' => $city->id,
            'places_id' => $place->id,
            'min_budget' => 100_000,
            'max_budget' => 800_000,
            'preferred_property_type' => 'apartment',
            'investment_goal' => InvestmentGoal::RentalIncome,
        ]);

        Estate::factory()->forOwner($owner)->count(5)->create([
            'places_id' => $place->id,
            'price' => 300_000,
            'type_text' => 'apartment',
            'roi' => 6,
            'status' => 'active',
        ]);

        $this->actingAsApiUser($user)->getJson($this->apiPrefix().'/recommendations?refresh=1');

        $response = $this->actingAsApiUser($user)
            ->getJson($this->apiPrefix().'/recommendations/top?limit=3');

        $this->assertApiSuccess($response);
        $this->assertLessThanOrEqual(3, count($response->json('data.recommendations')));
    }

    public function test_similar_estates_endpoint(): void
    {
        $user = User::factory()->create();
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

        Estate::factory()->forOwner($owner)->create([
            'places_id' => $place->id,
            'price' => 320_000,
            'type_text' => 'apartment',
            'num_of_bedrooms' => 3,
            'status' => 'active',
        ]);

        $response = $this->actingAsApiUser($user)
            ->getJson($this->apiPrefix().'/recommendations/similar-estates/'.$source->id);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.source_estate_id', $source->id);
        $response->assertJsonStructure([
            'data' => [
                'similar_estates' => [
                    '*' => [
                        'similarity_score',
                        'matching_percentage',
                        'why_recommended',
                        'estate',
                    ],
                ],
            ],
        ]);
    }

    public function test_guest_cannot_access_recommendations(): void
    {
        $this->getJson($this->apiPrefix().'/recommendations')->assertUnauthorized();
    }

    public function test_recommendations_based_on_favorite_estates(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $city = Cities::factory()->create();
        $place = Places::factory()->create(['cities_id' => $city->id]);

        $favorite = Estate::factory()->forOwner($owner)->create([
            'places_id' => $place->id,
            'price' => 300_000,
            'type_text' => 'apartment',
            'kind_text' => 'sale',
            'num_of_bedrooms' => 3,
            'status' => 'active',
        ]);

        $similar = Estate::factory()->forOwner($owner)->create([
            'places_id' => $place->id,
            'price' => 320_000,
            'type_text' => 'apartment',
            'kind_text' => 'sale',
            'num_of_bedrooms' => 3,
            'status' => 'active',
        ]);

        Estate::factory()->forOwner($owner)->create([
            'price' => 900_000,
            'type_text' => 'villa',
            'kind_text' => 'sale',
            'num_of_bedrooms' => 6,
            'status' => 'active',
        ]);

        Favorit_estate::query()->create([
            'user_id' => $user->id,
            'estate_id' => $favorite->id,
        ]);

        $response = $this->actingAsApiUser($user)
            ->getJson($this->apiPrefix().'/recommendations?refresh=1');

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.match_summary.has_favorite_estates', true);
        $response->assertJsonPath('data.match_summary.favorite_estates_count', 1);

        $recommendedIds = collect($response->json('data.recommendations'))->pluck('estate_id');
        $this->assertTrue($recommendedIds->contains($similar->id));
        $this->assertFalse($recommendedIds->contains($favorite->id));
        $this->assertGreaterThan(0, Recommendation::query()->where('user_id', $user->id)->count());
    }
}
