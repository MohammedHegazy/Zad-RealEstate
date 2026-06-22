<?php

namespace Tests\Feature\Trust;

use App\Models\Companies;
use App\Models\CompanyReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTrustApi;
use Tests\TestCase;

class CompanyReviewTest extends TestCase
{
    use InteractsWithTrustApi;
    use RefreshDatabase;

    public function test_user_can_submit_company_review(): void
    {
        $reviewer = User::factory()->create();
        $owner = User::factory()->create();
        $company = Companies::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAsApiUser($reviewer)->postJson(
            $this->apiPrefix().'/companies/'.$company->id.'/reviews',
            ['rating' => 5, 'review' => 'Great company']
        );

        $this->assertApiSuccess($response, 201);
        $this->assertDatabaseHas('company_reviews', [
            'company_id' => $company->id,
            'user_id' => $reviewer->id,
        ]);
    }

    public function test_company_average_rating(): void
    {
        $company = Companies::factory()->create();
        CompanyReview::factory()->approved()->create(['company_id' => $company->id, 'rating' => 5]);

        $response = $this->getJson($this->apiPrefix().'/companies/'.$company->id.'/reviews/summary');

        $this->assertApiSuccess($response);
        $this->assertEquals(5.0, $response->json('data.average_rating'));
    }
}
