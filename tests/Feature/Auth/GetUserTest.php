<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_do_not_return_without_user_logged()
    {
        $response = $this->postJson('/api/auth/user', []);

        $response->assertStatus(401);
    }

    public function test_return_user_logged_ok()
    {
        Role::factory()->count(2)->create();

        User::factory()->create([
            'name' => 'Celso Nery',
            'email' => 'celso@karyon.com.br',
            'role_id' => 2
        ]);

        $userLogged = $this->postJson('/api/auth/login', [
            'email' => 'celso@karyon.com.br',
            'password' => 'password'
        ]);

        $response = $this->withHeaders([
            'Authentication' => 'Bearer ' . $userLogged['token']
        ])->postJson('/api/auth/user', []);

        $response->assertStatus(200);
    }
}
