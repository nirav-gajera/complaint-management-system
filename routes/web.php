<?php

use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('api/auth')->group(function () {
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('guest');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('guest');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('guest');
    Route::get('/user', [EmailVerificationController::class, 'user']);

    Route::middleware('auth')->group(function () {
        Route::get('/email/status', [EmailVerificationController::class, 'status']);
        Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])->middleware('throttle:6,1');
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    });
});

Route::get('/reset-password/{token}', function (Request $request, string $token) {
    $email = $request->query('email');
    $query = $email ? '?email='.urlencode((string) $email) : '';

    return redirect('/reset-password/'.$token.$query);
})->middleware('guest')->name('password.reset');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/verify-email?verified=1');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::view('/{any?}', 'app')
    ->where('any', '^(?!api|sanctum|storage).*$');