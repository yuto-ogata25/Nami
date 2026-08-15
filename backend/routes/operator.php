<?php

use App\Http\Controllers\Auth\Operator\AuthenticatedSessionController;
use App\Http\Controllers\OperatorController;
use Illuminate\Support\Facades\Route;

// 運営者用ルート。/api/operator/* 配下、operator ガードで保護する。
// bootstrap/app.php の withRouting(then:) で 'api' ミドルウェア・prefix('api/operator') 付きで読み込まれる。

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest:operator');

Route::middleware('auth:operator')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::get('/me', [OperatorController::class, 'show']);
});
