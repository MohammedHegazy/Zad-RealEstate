<?php

namespace Tests\Feature\Admin;

use App\Models\Agent;
use App\Models\Companies;
use App\Models\Places;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTrustApi;
use Tests\TestCase;

class AdminCompanyManagementTest extends TestCase
{
    use InteractsWithTrustApi;
    use RefreshDatabase;

    public function test_admin_can_list_companies_with_filters(): void
    {
        $admin = User::factory()->admin()->create();
        Companies::factory()->approved()->create(['company_name' => 'Alpha Realty']);
        Companies::factory()->pending()->create(['company_name' => 'Beta Homes']);

        $response = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/companies?'.http_build_query([
                'search' => 'Alpha',
                'status' => 'approved',
            ]));

        $this->assertApiSuccess($response);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('Alpha Realty', $data[0]['company_name']);
    }

    public function test_admin_can_view_company_details(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Companies::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/companies/'.$company->id);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.id', $company->id);
        $response->assertJsonPath('data.company_name', $company->company_name);
    }

    public function test_admin_can_create_company_for_owner(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->company()->create();
        $place = Places::factory()->create();

        $payload = [
            'user_id' => $owner->id,
            'places_id' => $place->id,
            'company_name' => 'Admin Created Company',
            'website' => 'https://example.com',
            'employees_num' => 12,
            'description' => 'Created by admin panel.',
            'work_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
            'status' => 'pending',
        ];

        $response = $this->actingAsApiUser($admin)
            ->postJson($this->adminApiPrefix().'/companies', $payload);

        $this->assertApiSuccess($response, 201);
        $this->assertDatabaseHas('companies', [
            'user_id' => $owner->id,
            'company_name' => 'Admin Created Company',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_update_company_fields(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Companies::factory()->pending()->create(['company_name' => 'Old Name']);

        $response = $this->actingAsApiUser($admin)
            ->putJson($this->adminApiPrefix().'/companies/'.$company->id, [
                'company_name' => 'Updated Company Name',
                'employees_num' => 25,
            ]);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'company_name' => 'Updated Company Name',
            'employees_num' => 25,
        ]);
    }

    public function test_admin_can_update_company_status_via_patch(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Companies::factory()->pending()->create();

        $response = $this->actingAsApiUser($admin)
            ->patchJson($this->adminApiPrefix().'/companies/'.$company->id.'/status', [
                'status' => 'approved',
            ]);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_cannot_delete_company_with_agents(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Companies::factory()->create();
        Agent::factory()->forCompany($company)->create();

        $response = $this->actingAsApiUser($admin)
            ->deleteJson($this->adminApiPrefix().'/companies/'.$company->id);

        $response->assertStatus(422);
        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    public function test_admin_can_delete_company_without_agents(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Companies::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->deleteJson($this->adminApiPrefix().'/companies/'.$company->id);

        $this->assertApiSuccess($response);
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }
}
