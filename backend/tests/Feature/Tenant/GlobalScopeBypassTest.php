<?php

namespace Tests\Feature\Tenant;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * App\Auth\TenantAwareUserProvider の回帰テスト。
 * ログイン照合クエリに限って CompanyScope を外している箇所が
 * 「機能していること」と「他へ漏れていないこと」の両方を保証する。
 */
class GlobalScopeBypassTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_belonging_to_any_company_can_login(): void
    {
        // 複数社のユーザーが同時に存在する状態でも、
        // CompanyScope に妨げられず資格情報照合ができることを確認する。
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        User::factory()->for($companyA)->create();
        $userB = User::factory()->for($companyB)->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $userB->email,
            'password' => 'correct-password',
        ]);

        $response->assertOk();
        $this->assertAuthenticatedAs($userB, 'web');
    }

    public function test_user_all_after_login_only_returns_own_company_despite_bypass(): void
    {
        // ログイン照合用のバイパスが、ログイン後の通常クエリに漏れていないことを確認する。
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->for($companyA)->create();
        User::factory()->for($companyB)->count(2)->create();

        $this->actingAs($userA, 'web');

        $this->assertSame(1, User::count());
        $this->assertSame($companyA->id, User::first()->company_id);
    }
}
