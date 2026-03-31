<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComplaintStoreRequest;
use App\Http\Requests\ComplaintUpdateRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Complaint::class);

        $paginator = Complaint::query()
            ->with(['category', 'assignee', 'attachments', 'user'])
            ->where('user_id', $request->user()->id)
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->string('status')->value()))
            ->when($request->filled('category_id'), fn($query) => $query->where('complaint_category_id', $request->integer('category_id')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->value();
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('date_from'), fn($query) => $query->whereDate('created_at', '>=', $request->string('date_from')->value()))
            ->when($request->filled('date_to'), fn($query) => $query->whereDate('created_at', '<=', $request->string('date_to')->value()))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return ComplaintResource::collection($paginator)->response();
    }

    public function store(ComplaintStoreRequest $request): JsonResponse
    {
        $complaint = DB::transaction(function () use ($request) {
            $complaint = Complaint::query()->create([
                ...$request->safe()->except('attachments'),
                'user_id' => $request->user()->id,
                'reference' => $this->generateReference(),
                'submitted_at' => now(),
            ]);

            $this->storeAttachments($request, $complaint);

            AuditLogger::log('complaint.created', $complaint, null, $complaint->toArray(), 'Complaint submitted by user.', $request);

            return $complaint;
        });

        return response()->json([
            'message' => 'Complaint submitted successfully.',
            'data' => new ComplaintResource($this->loadComplaintForUser($complaint)),
        ], 201);
    }

    public function show(Request $request, Complaint $complaint): JsonResponse
    {
        $this->authorize('view', $complaint);

        return response()->json([
            'data' => new ComplaintResource($this->loadComplaintForUser($complaint)),
        ]);
    }

    public function update(ComplaintUpdateRequest $request, Complaint $complaint): JsonResponse
    {
        $oldValues = $complaint->only(['title', 'description', 'complaint_category_id']);

        DB::transaction(function () use ($request, $complaint, $oldValues) {
            $complaint->fill($request->safe()->except('attachments'));
            $complaint->save();

            $this->storeAttachments($request, $complaint);

            AuditLogger::log('complaint.updated', $complaint, $oldValues, $complaint->fresh()->toArray(), 'Complaint updated by user.', $request);
        });

        return response()->json([
            'message' => 'Complaint updated successfully.',
            'data' => new ComplaintResource($this->loadComplaintForUser($complaint->fresh())),
        ]);
    }

    public function destroy(Request $request, Complaint $complaint): JsonResponse
    {
        $this->authorize('delete', $complaint);

        $oldValues = $complaint->toArray();

        $complaint->update([
            'status' => Complaint::STATUS_REJECTED,
            'withdrawn_at' => now(),
            'withdrawal_reason' => $request->string('reason')->value() ?: 'Withdrawn by complainant.',
        ]);

        AuditLogger::log('complaint.withdrawn', $complaint, $oldValues, $complaint->fresh()->toArray(), 'Complaint withdrawn by user.', $request);

        return response()->json([
            'message' => 'Complaint withdrawn successfully.',
        ]);
    }

    public function download(Request $request, Complaint $complaint, ComplaintAttachment $attachment)
    {
        $this->authorize('view', $complaint);

        abort_unless($attachment->complaint_id === $complaint->id, 404);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    protected function generateReference(): string
    {
        return 'CMP-' . now()->format('Y') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    protected function loadComplaintForUser(Complaint $complaint): Complaint
    {
        return $complaint->load([
            'category',
            'assignee',
            'attachments',
            'user',
            'messages' => fn($query) => $query->where('is_internal', false)->with('user'),
        ]);
    }

    protected function storeAttachments(Request $request, Complaint $complaint): void
    {
        foreach ($request->file('attachments', []) as $file) {
            $path = $file->store('complaints/' . $complaint->id, 'local');

            $complaint->attachments()->create([
                'user_id' => $request->user()->id,
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }
    }
}
