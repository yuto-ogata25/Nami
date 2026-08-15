<?php

namespace Tests\Feature\Tenant;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SQLiteのインメモリDBでは外部キー制約の有無に気づきにくいため、
 * 本番・開発と同じMySQLでテストを実行し、テナント分離の連鎖が
 * どこかで途切れて「A社の部署がB社に紐づく」ような状態を作れないことを保証する。
 */
class ForeignKeyConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_be_created_with_nonexistent_company_id(): void
    {
        $this->expectException(QueryException::class);

        User::factory()->create(['company_id' => 999999]);
    }

    public function test_department_cannot_be_created_with_nonexistent_company_id(): void
    {
        $this->expectException(QueryException::class);

        Department::factory()->create(['company_id' => 999999]);
    }

    public function test_user_cannot_be_created_with_nonexistent_department_id(): void
    {
        $company = Company::factory()->create();

        $this->expectException(QueryException::class);

        User::factory()->for($company)->create(['department_id' => 999999]);
    }
}
