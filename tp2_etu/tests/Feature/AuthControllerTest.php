<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthControllerTest extends TestCase
{
    use DatabaseMigrations; 
    


    public function test_RegisterFailureMissingData()
    {
        
        
        $response = $this->postJson('/api/signup', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['login', 'password', 'email', 'last_name', 'first_name']);
    }

    
    public function test_RegisterSuccess()
    {
        $userData = [
            'login' => 'newuser',
            'password' => 'password123',
            'email' => 'newuser@example.com',
            'last_name' => 'User',
            'first_name' => 'New',
        ];

        $response = $this->postJson('/api/signup', $userData);

        $response->assertStatus(201); 
        $this->assertDatabaseHas('users', ['email' => $userData['email']]);

        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user);

        $response->assertJsonStructure(['token']);
    }

    
    public function test_LoginFailureWrongPassword()
    {
        
    Sanctum::actingAs(
        User::factory()->create(), ['*']
        );
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('correctPassword'),
        ]);

        $response = $this->postJson('/api/signin', [
            'email' => $user->email,
            'password' => 'wrongPassword',
        ]);

        $response->assertStatus(422); 
        $response->assertJson(['message' => 'The provided credentials are incorrect.']);
    }

    
    public function test_LoginSuccess()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/signin', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['email' => $user->email]);

        $user->refresh();
        $this->assertCount(1, $user->tokens);

        $response->assertJsonStructure(['token']);
    }

    
    public function test_LogoutWithoutBeingLoggedIn()
    {
        $response = $this->postJson('/api/signout');

        $response->assertStatus(401); 
    }

    
    public function test_LogoutSuccess()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']); 

        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/signout');

        $response->assertStatus(204);
        $user->refresh();
    }
}