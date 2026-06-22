<?php

namespace Tests\Feature\Admin;

use App\Models\Agent;
use App\Models\Companies;
use App\Models\Places;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTrustApi;
use Tests\TestCase;

class AdminAgentManagementTest extends TestCase
{
    use InteractsWithTrustApi;
    use RefreshDatabase;

    public function test_admin_can_list_agents_with_filters(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Companies::factory()->create(['company_name' => 'Alpha Realty Co']);
        $otherCompany = Companies::factory()->create();
        Agent::factory()->forCompany($company)->create();
        Agent::factory()->forCompany($otherCompany)->create();

        $response = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/agents?'.http_build_query([
                'companies_id' => $company->id,
            ]));

        $this->assertApiSuccess($response);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame($company->id, $data[0]['companies_id']);
    }

    public function test_admin_can_view_agent_details(): void
    {
        $admin = User::factory()->admin()->create();
        $agent = Agent::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/agents/'.$agent->id);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.id', $agent->id);
    }

    public function test_admin_can_create_agent_for_user_and_company(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->agent()->create();
        $company = Companies::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->postJson($this->adminApiPrefix().'/agents', [
                'user_id' => $owner->id,
                'companies_id' => $company->id,
            ]);

        $this->assertApiSuccess($response, 201);
        $this->assertDatabaseHas('agents', [
            'user_id' => $owner->id,
            'companies_id' => $company->id,
        ]);
    }

    public function test_admin_can_update_agent_company_and_trust_score(): void
    {
        $admin = User::factory()->admin()->create();
        $agent = Agent::factory()->create(['trust_score' => 0]);
        $newCompany = Companies::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->putJson($this->adminApiPrefix().'/agents/'.$agent->id, [
                'companies_id' => $newCompany->id,
                'trust_score' => 75,
            ]);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('agents', [
            'id' => $agent->id,
            'companies_id' => $newCompany->id,
            'trust_score' => 75,
        ]);
    }

    public function test_admin_can_delete_agent(): void
    {
        $admin = User::factory()->admin()->create();
        $agent = Agent::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->deleteJson($this->adminApiPrefix().'/agents/'.$agent->id);

        $this->assertApiSuccess($response);
        $this->assertDatabaseMissing('agents', ['id' => $agent->id]);
    }
}
