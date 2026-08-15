<?php

namespace App\Http\Controllers\Auth\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OperatorLoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function store(OperatorLoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return response()->json(['message' => 'ログインしました。']);
    }

    public function destroy(Request $request): JsonResponse
    {
        Auth::guard('operator')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'ログアウトしました。']);
    }
}
