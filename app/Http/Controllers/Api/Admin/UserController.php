<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStatusUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $paginator = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->value();
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn($query) => $query->where('role', $request->string('role')->value()))
            ->when($request->filled('is_active'), fn($query) => $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return UserResource::collection($paginator)->response();
    }

    public function update(UserStatusUpdateRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        AuditLog::query()->create([
            'user_id' => $request->user()->id,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'action' => 'user.updated',
            'description' => 'User account status updated.',
            'new_values' => $user->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => new UserResource($user->fresh()),
        ]);
    }

    public function resetPassword(User $user): JsonResponse
    {
        try {
            $status = Password::sendResetLink(['email' => $user->email]);
        } catch (Throwable $exception) {
            Log::channel('single')->error('Password reset email failed.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Password reset email could not be sent from this server.',
                'mail_error' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }
}