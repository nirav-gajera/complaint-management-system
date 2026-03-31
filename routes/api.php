<?php

use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    Route::middleware('verified')->group(function () {
        Route::get('/dashboard', DashboardController::class);
        Route::get('/complaints', [ComplaintController::class, 'index']);
        Route::post('/complaints', [ComplaintController::class, 'store']);
        Route::get('/complaints/{complaint}', [ComplaintController::class, 'show']);
        Route::put('/complaints/{complaint}', [ComplaintController::class, 'update']);
        Route::delete('/complaints/{complaint}', [ComplaintController::class, 'destroy']);
        Route::get('/complaints/{complaint}/attachments/{attachment}', [ComplaintController::class, 'download']);
    });

    Route::prefix('admin')->middleware('staff')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class);
        Route::get('/complaints', [AdminComplaintController::class, 'index']);
        Route::get('/complaints/{complaint}', [AdminComplaintController::class, 'show']);
        Route::patch('/complaints/{complaint}', [AdminComplaintController::class, 'update']);

        Route::get('/categories', [AdminCategoryController::class, 'index']);
        Route::post('/categories', [AdminCategoryController::class, 'store']);
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update']);
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);

        Route::get('/settings', [AdminSettingController::class, 'index']);
        Route::put('/settings', [AdminSettingController::class, 'update']);

        Route::get('/users', [AdminUserController::class, 'index']);
        Route::patch('/users/{user}', [AdminUserController::class, 'update']);
        Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword']);
    });
});
