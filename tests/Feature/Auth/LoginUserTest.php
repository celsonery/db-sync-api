<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_without_data()
    {
        $response = $this->postJson('/api/auth/login', ['', '']);

        $response->assertStatus(422);
    }

    public function test_data_empty()
    {
        $response = $this->postJson('/api/auth/login', ['email' => '', 'password' => '']);

        $response->assertStatus(422);
    }

    public function test_email_invalid()
    {
        $response = $this->postJson('/api/auth/login', ['email' => 'celso', 'password' => '']);

        $response->assertStatus(422);
    }

    public function test_password_without_min_length()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => '123']);

        $response->assertStatus(422);
    }

    public function test_password_without_max_length()
    {
        $user = User::factory()->create();
        $passMax = str_repeat("A", 256);

        $response = $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => $passMax]);

        $response->assertStatus(422);
    }

    public function test_with_password_wrong()
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => '123456']);

        $response->assertStatus(422);
    }

    public function test_with_correct_data()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password']);

        $response->assertStatus(200);
    }
}
