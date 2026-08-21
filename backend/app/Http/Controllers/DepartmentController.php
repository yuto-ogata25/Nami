<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DepartmentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return DepartmentResource::collection(
            Department::orderBy('name')->get()
        );
    }
}
