<?php

namespace Tests\Feature\Recommendations;

use App\Models\Cities;
use App\Models\Estate;
use App\Models\Places;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRecommendationApi;
use Tests\TestCase;

class UserPreferenceTest extends TestCase
{
    use InteractsWithRecommendationApi;
    use RefreshDatabase;

    public function test_user_can_save_preferences_with_preferred_city_id(): void
    {
        $user = User::factory()->create();
        $city = Cities::factory()->create();

        $response = $this->actingAsApiUser($user)->postJson($this->apiPrefix().'/my/preferences', [
            'preferred_city_id' => $city->id,
            'min_budget' => 150_000,
            'max_budget' => 600_000,
            'preferred_property_type' => 'apartment',
            'investment_goal' => 'rental_income',
            'risk_level' => 'moderate',
        ]);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.preferred_city_id', $city->id);
        $response->assertJsonPath('data.risk_level', 'moderate');

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'cities_id' => $city->id,
            'risk_level' => 'moderate',
        ]);
    }

    public function test_guest_cannot_save_preferences(): void
    {
        $this->postJson($this->apiPrefix().'/my/preferences', [
            'min_budget' => 100_000,
        ])->assertUnauthorized();
    }
}
