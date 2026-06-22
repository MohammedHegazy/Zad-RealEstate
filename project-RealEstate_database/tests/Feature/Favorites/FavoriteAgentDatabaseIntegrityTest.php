<?php

namespace Tests\Feature\Favorites;

use App\Models\Agent;
use App\Models\Favorit_agent;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteAgentDatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unique_user_agent_pair_constraint(): void
    {
        $user = User::factory()->create();
        $agent = Agent::factory()->create();

        Favorit_agent::query()->create([
            'user_id' => $user->id,
            'agent_id' => $agent->id,
        ]);

        $this->expectException(QueryException::class);

        Favorit_agent::query()->create([
            'user_id' => $user->id,
            'agent_id' => $agent->id,
        ]);
    }

    public function test_same_agent_can_be_favorited_by_different_users(): void
    {
        $agent = Agent::factory()->create();
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Favorit_agent::query()->create(['user_id' => $userA->id, 'agent_id' => $agent->id]);
        Favorit_agent::query()->create(['user_id' => $userB->id, 'agent_id' => $agent->id]);

        $this->assertDatabaseCount('favorite_agents', 2);
    }

    public function test_first_or_create_does_not_duplicate_favorite(): void
    {
        $user = User::factory()->create();
        $agent = Agent::factory()->create();

        Favorit_agent::query()->firstOrCreate([
            'user_id' => $user->id,
            'agent_id' => $agent->id,
        ]);

        Favorit_agent::query()->firstOrCreate([
            'user_id' => $user->id,
            'agent_id' => $agent->id,
        ]);

        $this->assertDatabaseCount('favorite_agents', 1);
    }

    public function test_deleting_user_cascades_favorite_agents(): void
    {
        $user = User::factory()->create();
        Favorit_agent::query()->create([
            'user_id' => $user->id,
            'agent_id' => Agent::factory()->create()->id,
        ]);

        $user->delete();

        $this->assertDatabaseCount('favorite_agents', 0);
    }

    public function test_deleting_agent_cascades_favorite_agents(): void
    {
        $agent = Agent::factory()->create();
        Favorit_agent::query()->create([
            'user_id' => User::factory()->create()->id,
            'agent_id' => $agent->id,
        ]);

        $agent->delete();

        $this->assertDatabaseCount('favorite_agents', 0);
    }
}
