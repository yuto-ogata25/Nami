<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_login_with_correct_credentials(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->for($company)->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response->assertOk();
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_customer_cannot_login_with_incorrect_password(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->for($company)->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $this->assertGuest('web');
    }

    public function test_authenticated_customer_can_fetch_current_user(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->for($company)->create();

        $response = $this->actingAs($user, 'web')->getJson('/api/user');

        $response->assertOk();
        $response->assertJsonPath('data.id', $user->id);
    }

    public function test_unauthenticated_customer_cannot_fetch_current_user(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_customer_can_logout(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->for($company)->create();

        $this->actingAs($user, 'web');

        $response = $this->postJson('/api/logout');

        $response->assertOk();
        $this->assertGuest('web');
    }
}
