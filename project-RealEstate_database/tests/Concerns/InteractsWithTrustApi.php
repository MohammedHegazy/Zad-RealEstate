<?php

namespace Tests\Concerns;

use App\Models\User;
use Illuminate\Testing\TestResponse;

trait InteractsWithTrustApi
{
    protected function apiPrefix(): string
    {
        return '/api/v1';
    }

    protected function adminApiPrefix(): string
    {
        return '/api/v1/admin';
    }

    protected function actingAsApiUser(User $user): static
    {
        return $this->actingAs($user, 'sanctum');
    }

    protected function assertApiSuccess(TestResponse $response, int $status = 200): void
    {
        $response->assertStatus($status);
        $response->assertJsonPath('success', true);
        $this->assertIsString($response->json('message'));
    }
}
