<?php

namespace App\Models\InternalCompetition;

use App\Enums\InternalCompetition\NotificationChannel;
use App\Enums\InternalCompetition\NotificationEvent;
use App\Enums\InternalCompetition\NotificationStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalCompetitionNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'recipient_type',
        'recipient_user_id',
        'tenant_id',
        'channel',
        'event_type',
        'subject',
        'content',
        'template_data',
        'status',
        'scheduled_at',
        'sent_at',
        'error_message',
        'external_id',
    ];

    protected $casts = [
        'channel' => NotificationChannel::class,
        'event_type' => NotificationEvent::class,
        'status' => NotificationStatus::class,
        'template_data' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(InternalCompetition::class, 'competition_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', NotificationStatus::PENDING);
    }

    public function scopeReadyToSend($query)
    {
        return $query->pending()
            ->where(fn($q) => $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now()));
    }

    public function markAsSent(?string $externalId = null): bool
    {
        return $this->update([
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
            'external_id' => $externalId,
        ]);
    }

    public function markAsFailed(string $errorMessage): bool
    {
        return $this->update([
            'status' => NotificationStatus::FAILED,
            'error_message' => $errorMessage,
        ]);
    }
}
