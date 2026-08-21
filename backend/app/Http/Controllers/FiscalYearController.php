<?php

namespace App\Http\Controllers;

use App\Http\Resources\FiscalYearResource;
use App\Models\FiscalYear;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FiscalYearController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return FiscalYearResource::collection(
            FiscalYear::orderByDesc('year')->get()
        );
    }
}
