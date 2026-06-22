<?php

namespace Tests\Feature\Trust;

use App\Models\Estate;
use App\Models\PropertyReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTrustApi;
use Tests\TestCase;

class PropertyReviewTest extends TestCase
{
    use InteractsWithTrustApi;
    use RefreshDatabase;

    public function test_user_can_submit_property_review(): void
    {
        $reviewer = User::factory()->create();
        $owner = User::factory()->create();
        $estate = Estate::factory()->forOwner($owner)->create(['status' => 'active']);

        $response = $this->actingAsApiUser($reviewer)->postJson(
            $this->apiPrefix().'/estates/'.$estate->id.'/reviews',
            ['rating' => 5, 'review' => 'Excellent property']
        );

        $this->assertApiSuccess($response, 201);
        $this->assertDatabaseHas('property_reviews', [
            'user_id' => $reviewer->id,
            'estate_id' => $estate->id,
            'rating' => 5,
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_review_same_property_twice(): void
    {
        $reviewer = User::factory()->create();
        $owner = User::factory()->create();
        $estate = Estate::factory()->forOwner($owner)->create(['status' => 'active']);

        PropertyReview::factory()->create([
            'user_id' => $reviewer->id,
            'estate_id' => $estate->id,
        ]);

        $this->actingAsApiUser($reviewer)->postJson(
            $this->apiPrefix().'/estates/'.$estate->id.'/reviews',
            ['rating' => 4, 'review' => 'Duplicate']
        )->assertStatus(422);
    }

    public function test_public_list_shows_only_approved_reviews(): void
    {
        $owner = User::factory()->create();
        $estate = Estate::factory()->forOwner($owner)->create(['status' => 'active']);

        PropertyReview::factory()->approved()->create(['estate_id' => $estate->id]);
        PropertyReview::factory()->create(['estate_id' => $estate->id, 'status' => 'pending']);

        $response = $this->getJson($this->apiPrefix().'/estates/'.$estate->id.'/reviews');

        $this->assertApiSuccess($response);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_average_rating_summary(): void
    {
        $owner = User::factory()->create();
        $estate = Estate::factory()->forOwner($owner)->create(['status' => 'active']);

        PropertyReview::factory()->approved()->create(['estate_id' => $estate->id, 'rating' => 4]);
        PropertyReview::factory()->approved()->create(['estate_id' => $estate->id, 'rating' => 5]);

        $response = $this->getJson($this->apiPrefix().'/estates/'.$estate->id.'/reviews/summary');

        $this->assertApiSuccess($response);
        $this->assertSame(4.5, $response->json('data.average_rating'));
        $this->assertSame(2, $response->json('data.reviews_count'));
    }
}
