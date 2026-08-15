<?php

namespace App\Http\Controllers;

use App\Http\Resources\OperatorResource;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function show(Request $request): OperatorResource
    {
        return new OperatorResource($request->user('operator'));
    }
}
