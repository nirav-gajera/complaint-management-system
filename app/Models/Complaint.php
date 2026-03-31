<?php

namespace App\Models;

use App\Policies\ComplaintPolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[UsePolicy(ComplaintPolicy::class)]
class Complaint extends Model
{
    /** @use HasFactory<\Database\Factories\ComplaintFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'complaint_category_id',
        'assigned_to',
        'reference',
        'title',
        'description',
        'status',
        'priority',
        'submitted_at',
        'first_admin_action_at',
        'resolved_at',
        'withdrawn_at',
        'withdrawal_reason',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'first_admin_action_at' => 'datetime',
            'resolved_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'complaint_category_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ComplaintAttachment::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ComplaintMessage::class)->latest();
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'auditable_id')
            ->where('auditable_type', self::class)
            ->latest();
    }

    public function canBeEditedBy(User $user): bool
    {
        return $this->user_id === $user->id
            && $this->first_admin_action_at === null
            && $this->withdrawn_at === null
            && $this->status === self::STATUS_PENDING;
    }

    public function canBeWithdrawnBy(User $user): bool
    {
        return $this->canBeEditedBy($user);
    }
}
