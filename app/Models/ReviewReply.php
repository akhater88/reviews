<?php

namespace App\Models;

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
        'is_published',
        'published_at',
        'google_reply_id',
    ];

    protected $casts = [
        'is_ai_generated' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
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
     * Mark as published.
     */
    public function markAsPublished(?string $googleReplyId = null): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => now(),
            'google_reply_id' => $googleReplyId,
        ]);

        // Also mark the review as replied
        $this->review->markAsReplied();
    }

    /**
     * Scope for published replies.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for AI-generated replies.
     */
    public function scopeAiGenerated($query)
    {
        return $query->where('is_ai_generated', true);
    }
}
