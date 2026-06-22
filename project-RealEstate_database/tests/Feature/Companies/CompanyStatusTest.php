<?php

namespace Tests\Feature\Companies;

use App\Models\Companies;
use App\Models\Places;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithSocialLinkApi;
use Tests\TestCase;

class CompanyStatusTest extends TestCase
{
    use InteractsWithSocialLinkApi;
    use RefreshDatabase;

    public function test_user_created_company_starts_as_pending(): void
    {
        $owner = User::factory()->create();
        $place = Places::factory()->create();

        $response = $this->actingAsApiUser($owner)->postJson($this->apiPrefix().'/my/company', [
            'places_id' => $place->id,
            'company_name' => 'Acme Realty',
            'description' => 'Full-service brokerage.',
            'work_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
        ]);

        $this->assertApiSuccess($response, 201);
        $response->assertJsonPath('data.status', 'pending');
        $response->assertJsonPath('data.work_days', ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday']);

        $this->assertDatabaseHas('companies', [
            'company_name' => 'Acme Realty',
            'status' => 'pending',
        ]);
    }

    public function test_public_directory_lists_only_approved_companies(): void
    {
        Companies::factory()->approved()->create(['company_name' => 'Visible Co']);
        Companies::factory()->pending()->create(['company_name' => 'Hidden Co']);

        $response = $this->getJson($this->apiPrefix().'/companies');

        $this->assertApiSuccess($response);
        $names = collect($response->json('data'))->pluck('company_name')->all();
        $this->assertContains('Visible Co', $names);
        $this->assertNotContains('Hidden Co', $names);
    }

    public function test_public_show_hides_non_approved_company(): void
    {
        $company = Companies::factory()->pending()->create();

        $response = $this->getJson($this->apiPrefix().'/companies/'.$company->id);

        $this->assertApiError($response, 404);
    }

    public function test_owner_can_view_own_pending_company_profile(): void
    {
        $owner = User::factory()->create();
        $company = Companies::factory()->forOwner($owner)->pending()->create();

        $response = $this->actingAsApiUser($owner)->getJson($this->apiPrefix().'/my/company');

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.id', $company->id);
        $response->assertJsonPath('data.status', 'pending');
    }

    public function test_user_without_company_gets_not_found_on_my_company(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsApiUser($user)->getJson($this->apiPrefix().'/my/company');

        $this->assertApiError($response, 404);
    }

    public function test_user_cannot_create_second_company_profile(): void
    {
        $owner = User::factory()->create();
        $place = Places::factory()->create();
        Companies::factory()->forOwner($owner)->create();

        $response = $this->actingAsApiUser($owner)->postJson($this->apiPrefix().'/my/company', [
            'places_id' => $place->id,
            'company_name' => 'Second Co',
            'description' => 'Should fail.',
            'work_days' => ['Sunday', 'Monday'],
        ]);

        $this->assertValidationFailed($response);
    }

    public function test_admin_can_update_company_status(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Companies::factory()->pending()->create();

        $response = $this->actingAsApiUser($admin)->patchJson(
            $this->adminApiPrefix().'/companies/'.$company->id.'/status',
            ['status' => 'approved'],
        );

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.status', 'approved');
        $this->assertDatabaseHas('companies', ['id' => $company->id, 'status' => 'approved']);
    }

    public function test_legacy_work_days_string_is_normalized_on_create(): void
    {
        $owner = User::factory()->create();
        $place = Places::factory()->create();

        $response = $this->actingAsApiUser($owner)->postJson($this->apiPrefix().'/my/company', [
            'places_id' => $place->id,
            'company_name' => 'Legacy Days Co',
            'description' => 'Testing legacy input.',
            'work_days' => 'Sunday,Monday,Wednesday,Friday',
        ]);

        $this->assertApiSuccess($response, 201);
        $response->assertJsonPath('data.work_days', ['Sunday', 'Monday', 'Wednesday', 'Friday']);
    }

    public function test_invalid_work_day_is_rejected(): void
    {
        $owner = User::factory()->create();
        $place = Places::factory()->create();

        $response = $this->actingAsApiUser($owner)->postJson($this->apiPrefix().'/my/company', [
            'places_id' => $place->id,
            'company_name' => 'Bad Days Co',
            'description' => 'Invalid schedule.',
            'work_days' => ['Funday'],
        ]);

        $this->assertValidationFailed($response);
    }
}
