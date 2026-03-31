<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ComplaintStatusUpdateRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Models\User;
use App\Notifications\ComplaintStatusUpdated;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ComplaintController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $paginator = Complaint::query()
            ->with(['category', 'assignee', 'attachments', 'user'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->value()))
            ->when($request->filled('category_id'), fn ($query) => $query->where('complaint_category_id', $request->integer('category_id')))
            ->when($request->filled('assigned_to'), fn ($query) => $query->where('assigned_to', $request->integer('assigned_to')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->value();
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return ComplaintResource::collection($paginator)->response();
    }

    public function show(Complaint $complaint): JsonResponse
    {
        return response()->json([
            'data' => new ComplaintResource($complaint->load(['category', 'assignee', 'attachments', 'messages.user', 'auditLogs.user', 'user'])),
            'staff' => User::query()->whereIn('role', [User::ROLE_ADMIN, User::ROLE_MANAGER, User::ROLE_SUPPORT_AGENT])->orderBy('name')->get(['id', 'name', 'email', 'role']),
        ]);
    }

    public function update(ComplaintStatusUpdateRequest $request, Complaint $complaint): JsonResponse
    {
        $oldValues = $complaint->only(['status', 'assigned_to', 'resolved_at', 'first_admin_action_at']);
        $mailWarning = null;
        $mailError = null;

        DB::transaction(function () use ($request, $complaint, $oldValues, &$mailWarning, &$mailError) {
            $statusChanged = $complaint->status !== $request->string('status')->value();

            $complaint->fill([
                'status' => $request->string('status')->value(),
                'assigned_to' => $request->input('assigned_to'),
            ]);

            if ($complaint->first_admin_action_at === null) {
                $complaint->first_admin_action_at = now();
            }

            $complaint->resolved_at = $complaint->status === Complaint::STATUS_RESOLVED ? now() : null;
            $complaint->save();

            if ($request->filled('public_message')) {
                $complaint->messages()->create([
                    'user_id' => $request->user()->id,
                    'is_internal' => false,
                    'message' => $request->string('public_message')->value(),
                ]);
            }

            if ($request->filled('internal_note')) {
                $complaint->messages()->create([
                    'user_id' => $request->user()->id,
                    'is_internal' => true,
                    'message' => $request->string('internal_note')->value(),
                ]);
            }

            if ($statusChanged) {
                try {
                    $complaint->user->notify(new ComplaintStatusUpdated(
                        $complaint,
                        $request->string('public_message')->value() ?: 'Your complaint status has been updated by our support team.'
                    ));
                } catch (Throwable $exception) {
                    Log::channel('single')->error('Complaint status update email failed.', [
                        'complaint_id' => $complaint->id,
                        'user_id' => $complaint->user_id,
                        'email' => $complaint->user?->email,
                        'message' => $exception->getMessage(),
                    ]);

                    $mailWarning = 'Complaint was updated, but the email notification could not be sent from this server.';
                    $mailError = $exception->getMessage();
                }
            }

            AuditLogger::log('complaint.admin_updated', $complaint, $oldValues, $complaint->fresh()->toArray(), 'Complaint updated by admin portal.', $request);
        });

        return response()->json([
            'message' => 'Complaint updated successfully.',
            'warning' => $mailWarning,
            'mail_error' => $mailError,
            'data' => new ComplaintResource($complaint->fresh()->load(['category', 'assignee', 'attachments', 'messages.user', 'auditLogs.user', 'user'])),
        ]);
    }
}