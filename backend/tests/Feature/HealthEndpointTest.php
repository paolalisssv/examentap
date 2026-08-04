<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_health_endpoint_returns_standardized_success_response(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'message' => 'OK',
            ])
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data' => ['app', 'timestamp'],
                'errors',
            ]);
    }
}
