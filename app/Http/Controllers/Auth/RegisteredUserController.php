<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class RegisteredUserController extends Controller
{
    public function store(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create($request->validated());
        $mailWarning = null;
        $mailError = null;

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            Log::channel('single')->error('Verification email could not be sent after registration.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $exception->getMessage(),
            ]);

            $mailWarning = 'Account created, but the verification email could not be sent from this server.';
            $mailError = $exception->getMessage();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Registration completed successfully.',
            'mail_warning' => $mailWarning,
            'mail_error' => $mailError,
            'user' => new UserResource($user),
        ], 201);
    }
}