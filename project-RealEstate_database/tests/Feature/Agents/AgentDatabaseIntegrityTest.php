<?php

namespace Tests\Feature\Agents;

use App\Models\Agent;
use App\Models\Companies;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentDatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unique_user_id_constraint_prevents_duplicate_agent_profiles(): void
    {
        $user = User::factory()->create();
        $company = Companies::factory()->create();

        Agent::query()->create([
            'user_id' => $user->id,
            'companies_id' => $company->id,
        ]);

        $this->expectException(QueryException::class);

        Agent::query()->create([
            'user_id' => $user->id,
            'companies_id' => $company->id,
        ]);
    }

    public function test_user_has_one_agent_relationship(): void
    {
        $user = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->agent->is($agent));
        $this->assertTrue($agent->user->is($user));
    }

    public function test_deleting_user_cascades_agent_profile(): void
    {
        $user = User::factory()->create();
        Agent::factory()->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseCount('agents', 0);
    }

    public function test_deleting_company_cascades_agents(): void
    {
        $company = Companies::factory()->create();
        Agent::factory()->forCompany($company)->create();

        $company->delete();

        $this->assertDatabaseCount('agents', 0);
    }
}
