<?php

use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard/my-applications', [DashboardController::class, 'myApplications']);
    Route::get('/dashboard/effective-access', [DashboardController::class, 'effectiveAccessMatrix']);

    Route::apiResource('applications', ApplicationController::class);

    Route::get('/applications/{application}/access', [AccessController::class, 'show']);
    Route::put('/applications/{application}/access/departments', [AccessController::class, 'syncDepartments']);
    Route::put('/applications/{application}/access/roles', [AccessController::class, 'syncRoles']);
    Route::put('/applications/{application}/access/users', [AccessController::class, 'syncUsers']);
});