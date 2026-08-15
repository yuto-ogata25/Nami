<?php

namespace Tests\Feature\Auth;

use App\Models\Operator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OperatorLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_login_with_correct_credentials(): void
    {
        $operator = Operator::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson('/api/operator/login', [
            'email' => $operator->email,
            'password' => 'correct-password',
        ]);

        $response->assertOk();
        $this->assertAuthenticatedAs($operator, 'operator');
    }

    public function test_operator_cannot_login_with_incorrect_password(): void
    {
        $operator = Operator::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson('/api/operator/login', [
            'email' => $operator->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $this->assertGuest('operator');
    }

    public function test_authenticated_operator_can_fetch_current_operator(): void
    {
        $operator = Operator::factory()->create();

        $response = $this->actingAs($operator, 'operator')->getJson('/api/operator/me');

        $response->assertOk();
        $response->assertJsonPath('data.id', $operator->id);
    }

    public function test_unauthenticated_operator_cannot_fetch_current_operator(): void
    {
        $response = $this->getJson('/api/operator/me');

        $response->assertStatus(401);
    }

    public function test_operator_can_logout(): void
    {
        $operator = Operator::factory()->create();

        $this->actingAs($operator, 'operator');

        $response = $this->postJson('/api/operator/logout');

        $response->assertOk();
        $this->assertGuest('operator');
    }
}
