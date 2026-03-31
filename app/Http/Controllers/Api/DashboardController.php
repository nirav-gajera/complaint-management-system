<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $complaints = Complaint::query()->where('user_id', $request->user()->id);

        return response()->json([
            'stats' => [
                'total' => (clone $complaints)->count(),
                'pending' => (clone $complaints)->where('status', Complaint::STATUS_PENDING)->count(),
                'in_progress' => (clone $complaints)->where('status', Complaint::STATUS_IN_PROGRESS)->count(),
                'resolved' => (clone $complaints)->where('status', Complaint::STATUS_RESOLVED)->count(),
                'rejected' => (clone $complaints)->where('status', Complaint::STATUS_REJECTED)->count(),
            ],
            'recent_complaints' => ComplaintResource::collection(
                (clone $complaints)
                    ->with(['category', 'assignee', 'attachments'])
                    ->latest()
                    ->take(5)
                    ->get()
            ),
        ]);
    }
}
