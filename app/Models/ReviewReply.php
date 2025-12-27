<?php

namespace App\Models;

use App\Enums\ReplyStatus;
use App\Enums\ReplyTone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'user_id',
        'reply_text',
        'is_ai_generated',
        'ai_tone',
        'ai_provider',
        'ai_model',
        'status',
        'is_published',
        'published_at',
        'google_reply_id',
        'error_message',
        'tokens_used',
    ];

    protected $casts = [
        'is_ai_generated' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'status' => ReplyStatus::class,
        'tokens_used' => 'integer',
    ];

    /**
     * Get the review this reply belongs to.
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Get the user who created this reply.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get tone display in Arabic.
     */
    public function getToneDisplayAttribute(): string
    {
        return match($this->ai_tone) {
            'professional' => 'مهني',
            'friendly' => 'ودي',
            'apologetic' => 'اعتذاري',
            'grateful' => 'شكر وتقدير',
            'neutral' => 'محايد',
            default => $this->ai_tone ?? 'يدوي',
        };
    }

    /**
     * Get the tone enum if valid.
     */
    public function getToneEnumAttribute(): ?ReplyTone
    {
        return ReplyTone::tryFrom($this->ai_tone);
    }

    /**
     * Check if reply is published.
     */
    public function isPublished(): bool
    {
        return $this->status === ReplyStatus::PUBLISHED || $this->is_published;
    }

    /**
     * Check if reply is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === ReplyStatus::DRAFT;
    }

    /**
     * Check if reply failed to publish.
     */
    public function isFailed(): bool
    {
        return $this->status === ReplyStatus::FAILED;
    }

    /**
     * Check if reply can be published.
     */
    public function canPublish(): bool
    {
        return in_array($this->status, [ReplyStatus::DRAFT, ReplyStatus::FAILED])
            && !empty($this->reply_text);
    }

    /**
     * Check if reply can be edited.
     */
    public function canEdit(): bool
    {
        return $this->status !== ReplyStatus::PUBLISHED && !$this->is_published;
    }

    /**
     * Mark as published.
     */
    public function markAsPublished(?string $googleReplyId = null): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => now(),
            'google_reply_id' => $googleReplyId,
            'status' => ReplyStatus::PUBLISHED,
            'error_message' => null,
        ]);

        // Also mark the review as replied
        $this->review->markAsReplied();
    }

    /**
     * Mark as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => ReplyStatus::FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Scope for published replies.
     */
    public function scopePublished($query)
    {
        return $query->where(function ($q) {
            $q->where('is_published', true)
              ->orWhere('status', ReplyStatus::PUBLISHED);
        });
    }

    /**
     * Scope for draft replies.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', ReplyStatus::DRAFT);
    }

    /**
     * Scope for failed replies.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', ReplyStatus::FAILED);
    }

    /**
     * Scope for AI-generated replies.
     */
    public function scopeAiGenerated($query)
    {
        return $query->where('is_ai_generated', true);
    }
}
