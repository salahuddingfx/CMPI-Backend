<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    try {
        DB::connection()->getPdo();
        $databaseConnected = true;
    } catch (Throwable $e) {
        $databaseConnected = false;
    }

    return view('welcome', [
        'apiBase' => url('/api/v1'),
        'environment' => app()->environment(),
        'laravel' => app()->version(),
        'php' => PHP_VERSION,
        'dbConnection' => config('database.default'),
        'databaseConnected' => $databaseConnected,
        'clientUrl' => config('app.client_url'),
        'adminUrl' => config('app.admin_url'),
    ]);
})->name('home');

Route::post('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');
