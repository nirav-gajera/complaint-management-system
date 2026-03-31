<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailVerificationController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        return response()->json([
            'verified' => $request->user()?->hasVerifiedEmail() ?? false,
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email address is already verified.',
            ]);
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            Log::channel('single')->error('Verification email resend failed.', [
                'user_id' => $request->user()?->id,
                'email' => $request->user()?->email,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Verification email could not be sent from this server. Check SMTP access and Mailtrap settings.',
                'mail_error' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Verification link sent.',
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user() ? new UserResource($request->user()) : null,
        ]);
    }
}