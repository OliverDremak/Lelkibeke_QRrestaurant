<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshApplication();
        RateLimiter::clear('api');
    }

    public function test_rate_limiting()
    {
        for ($i = 0; $i < 21; $i++) {
            $response = $this->withHeaders([
                'Accept' => 'application/json',
            ])->get('/api/menu');

            if ($i < 20) {
                $response->assertSuccessful();
            } else {
                $response->assertStatus(429);
            }
        }
    }

    public function test_rate_limit_headers()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->get('/api/menu');

        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }
}
