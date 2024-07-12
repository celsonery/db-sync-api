<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_if_api_running_ok()
    {
        $response = $this->get('/api');

        $response->assertStatus(200);
    }
}
