<?php

namespace Tests\Feature\Companies;

use App\Models\Companies;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyDatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unique_user_id_constraint_prevents_duplicate_company_profiles(): void
    {
        $user = User::factory()->create();

        Companies::factory()->forOwner($user)->create();

        $this->expectException(QueryException::class);

        Companies::factory()->forOwner($user)->create();
    }

    public function test_user_has_one_company_relationship(): void
    {
        $user = User::factory()->create();
        $company = Companies::factory()->forOwner($user)->create();

        $this->assertTrue($user->company->is($company));
        $this->assertTrue($company->user->is($user));
    }

    public function test_deleting_user_cascades_company_profile(): void
    {
        $user = User::factory()->create();
        Companies::factory()->forOwner($user)->create();

        $user->delete();

        $this->assertDatabaseCount('companies', 0);
    }
}
