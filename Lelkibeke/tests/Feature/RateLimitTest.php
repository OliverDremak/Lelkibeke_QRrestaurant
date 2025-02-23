<?php

namespace Tests\Feature;

use Tests\TestCase;

class RateLimitTest extends TestCase
{
    public function test_rate_limiting()
    {
        // Send 61 requests (1 over limit)
        for ($i = 0; $i < 61; $i++) {
            $response = $this->get('/api/menu');
            
            if ($i < 60) {
                $response->assertSuccessful();
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    public function test_rate_limit_headers()
    {
        $response = $this->get('/api/menu');
        
        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }
}
