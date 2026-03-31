<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function show(\Illuminate\Http\Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => new UserResource($request->user()->fresh()),
        ]);
    }

    public function updatePassword(PasswordUpdateRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => $request->string('password')->value(),
        ]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}
