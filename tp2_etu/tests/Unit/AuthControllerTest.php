<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    use DatabaseMigrations;
    


    public function testRegisterFailureMissingData()
    {
        
        Sanctum::actingAs(
        User::factory()->create(), ['*']
        );
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['login', 'password', 'email', 'last_name', 'first_name']);
    }

    
    public function testRegisterSuccess()
    {
        $userData = [
            'login' => 'newuser',
            'password' => 'password123',
            'email' => 'newuser@example.com',
            'last_name' => 'User',
            'first_name' => 'New',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(200); 
        $this->assertDatabaseHas('users', ['email' => $userData['email']]);

        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user);
        $this->assertCount(1, $user->tokens);

        $response->assertJsonStructure(['token']);
    }

    
    public function testLoginFailureWrongPassword()
    {
        
    Sanctum::actingAs(
        User::factory()->create(), ['*']
        );
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('correctPassword'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongPassword',
        ]);

        $response->assertStatus(422); 
        $response->assertJson(['message' => 'The provided credentials are incorrect.']);
    }

    
    public function testLoginSuccess()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200); // OK
        $this->assertDatabaseHas('users', ['email' => $user->email]);

        $user->refresh();
        $this->assertCount(1, $user->tokens);

        $response->assertJsonStructure(['token']);
    }

    
    public function testLogoutWithoutBeingLoggedIn()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401); 
    }

    
    public function testLogoutSuccess()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']); 

        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(204);
        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }
}
