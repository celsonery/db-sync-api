<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_do_not_register_without_data()
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422);
    }

    public function test_do_not_register_with_data_empty()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => ''
        ]);

        $response->assertStatus(422);
    }

    public function test_do_not_register_with_name_without_min_length()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'ce',
            'email' => 'celso@karyon.com.br',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(422);
    }

    public function test_do_not_register_with_name_without_max_length()
    {
        $nameMax = str_repeat("A", 256);

        $response = $this->postJson('/api/auth/register', [
            'name' => $nameMax,
            'email' => 'celso@karyon.com.br',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(422);
    }

    public function test_do_not_register_with_email_invalid()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'celso',
            'email' => 'celso',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(422);
    }

    public function test_do_not_register_password_without_min_length()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'celso',
            'email' => 'celso@karyon.com.br',
            'password' => 'pas',
            'password_confirmation' => 'pas'
        ]);

        $response->assertStatus(422);
    }

    public function test_do_not_register_password_without_max_length()
    {
        $passMax = str_repeat("A", 256);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'celso',
            'email' => 'celso@karyon.com.br',
            'password' => $passMax,
            'password_confirmation' => $passMax
        ]);

        $response->assertStatus(422);
    }

    public function test_do_not_register_password_and_confirmation_different()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'celso',
            'email' => 'celso@karyon.com.br',
            'password' => 'password',
            'password_confirmation' => 'password_diff'
        ]);

        $response->assertStatus(422);
    }

    public function test_register_with_correct_data()
    {
        Role::factory()->count(2)->create();

        $user = User::factory()->create([
            'name' => 'Celso Nery',
            'email' => 'celso@karyon.com.br'
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'celso@karyon.com.br',
            'password' => 'password'
        ]);

        $response->assertOk();
    }
}
