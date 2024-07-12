<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LogoutUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_do_not_logout_without_user_logged()
    {
        $response = $this->postJson('/api/auth/logout', []);

        $response->assertStatus(401);
    }

    public function test_logout_user_logged_ok()
    {
        $user = User::factory()->create([
            'name' => 'Celso Nery',
            'email' => 'celso@karyon.com.br'
        ]);

        $userLogged = $this->postJson('/api/auth/login', [
            'email' => 'celso@karyon.com.br',
            'password' => 'password'
        ]);

        $response = $this->withHeaders([
            'Authentication' => 'Bearer ' . $userLogged['token']
        ])->postJson('/api/auth/logout', []);

        $response->assertStatus(200);
    }

}
