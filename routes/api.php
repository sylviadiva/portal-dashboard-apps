<?php

use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReferenceController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::get('/dashboard/my-applications', [DashboardController::class, 'myApplications']);

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard/effective-access', [DashboardController::class, 'effectiveAccessMatrix']);
        Route::get('/references', [ReferenceController::class, 'index']);

        Route::apiResource('applications', ApplicationController::class);
        Route::apiResource('users', UserController::class)->except(['show']);

        Route::get('/applications/{application}/access', [AccessController::class, 'show']);
        Route::put('/applications/{application}/access/departments', [AccessController::class, 'syncDepartments']);
        Route::put('/applications/{application}/access/roles', [AccessController::class, 'syncRoles']);
        Route::put('/applications/{application}/access/users', [AccessController::class, 'syncUsers']);
    });
});