<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/health', function () {
    $version = DB::select('SELECT VERSION() as version')[0]->version;
    return response()->json([
        'status' => 'ok',
        'db_version' => $version,
    ]);
});
