<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $response = $this->postJson('/api/auth/login', ['email' => 'celso@karyon.com.br', 'password' => '123']);

        $response->assertStatus(422);
    }

    public function test_password_without_max_length()
    {
        $passMax = str_repeat("A", 256);

        $response = $this->postJson('/api/auth/login', ['email' => 'celso@karyon.com.br', 'password' => $passMax]);

        $response->assertStatus(422);
    }

    public function test_with_password_wrong()
    {
        $response = $this->postJson('/api/auth/login', ['email' => 'celso@karyon.com.br', 'password' => '123456']);

        $response->assertStatus(422);
    }

    public function test_with_correct_data()
    {
        $user = User::factory()->create([
            'email' => 'celso@karyon.com.br'
        ]);

        $response = $this->postJson('/api/auth/login', ['email' => 'celso@karyon.com.br', 'password' => 'password']);

        $response->assertStatus(200);
    }
}
