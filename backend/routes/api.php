<?php

use App\Http\Controllers\Auth\Customer\AuthenticatedSessionController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FiscalYearController;
use App\Http\Controllers\StrategyGoalController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// ALBのヘルスチェック対象。認証で塞ぐとECSタスクが全てunhealthy判定になるため、常に公開のままにする。
Route::get('/health', function () {
    DB::select('select 1');

    return response()->json([
        'status' => 'ok',
        'db_driver' => DB::connection()->getDriverName(),
    ]);
});

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest:web');

Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::get('/user', [UserController::class, 'show']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::get('/fiscal-years', [FiscalYearController::class, 'index']);

    Route::apiResource('strategy-goals', StrategyGoalController::class);
});
