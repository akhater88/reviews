<?php

namespace App\Livewire;

use App\Enums\ReplyStatus;
use App\Enums\ReplyTone;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Services\ReplyService;
use App\Services\GoogleReplyService;
use Livewire\Component;
use Livewire\Attributes\On;

class ReplyModal extends Component
{
    public ?Review $review = null;
    public ?ReviewReply $reply = null;

    public string $replyText = '';
    public string $tone = 'professional';
    public bool $isGenerating = false;
    public bool $isPublishing = false;
    public bool $isSaving = false;
    public bool $showModal = false;
    public ?string $error = null;
    public ?string $success = null;
    public bool $canPublishToGoogle = false;
    public ?string $connectionStatus = null;

    #[On('openReplyModal')]
    public function open(int $reviewId): void
    {
        $this->resetState();

        $this->review = Review::with(['branch', 'reply'])->findOrFail($reviewId);
        $this->reply = $this->review->reply;
        $this->replyText = $this->reply?->reply_text ?? '';
        $this->tone = $this->reply?->ai_tone ?? 'professional';
        $this->showModal = true;

        // Check Google connection status
        $googleService = app(GoogleReplyService::class);
        $status = $googleService->getConnectionStatus($this->review->branch_id);
        $this->canPublishToGoogle = $status['can_publish'] && !empty($this->review->google_review_id);
        $this->connectionStatus = $status['message'];
    }

    public function close(): void
    {
        $this->showModal = false;
        $this->resetState();
        $this->dispatch('replyModalClosed');
    }

    private function resetState(): void
    {
        $this->review = null;
        $this->reply = null;
        $this->replyText = '';
        $this->tone = 'professional';
        $this->error = null;
        $this->success = null;
        $this->isGenerating = false;
        $this->isPublishing = false;
        $this->isSaving = false;
    }

    public function setTone(string $tone): void
    {
        $this->tone = $tone;
        $this->error = null;
    }

    public function generateReply(): void
    {
        $this->isGenerating = true;
        $this->error = null;
        $this->success = null;

        try {
            $service = app(ReplyService::class);
            $toneEnum = ReplyTone::from($this->tone);

            $result = $service->generateReply(
                $this->review,
                $toneEnum,
                $this->review->branch?->name
            );

            $this->replyText = $result['reply_text'];

            // Save as draft
            $this->reply = $service->saveReply(
                $this->review,
                $this->replyText,
                $toneEnum,
                true,
                $result['provider'],
                $result['model'],
                $result['tokens_used']
            );

            $this->success = 'تم إنشاء الرد بنجاح';

        } catch (\Exception $e) {
            $this->error = 'فشل في إنشاء الرد: ' . $e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    public function saveDraft(): void
    {
        if (empty(trim($this->replyText))) {
            $this->error = 'الرجاء كتابة رد أولاً';
            return;
        }

        $this->isSaving = true;
        $this->error = null;

        try {
            $service = app(ReplyService::class);
            $toneEnum = ReplyTone::tryFrom($this->tone) ?? ReplyTone::PROFESSIONAL;

            $this->reply = $service->saveReply(
                $this->review,
                $this->replyText,
                $toneEnum,
                $this->reply?->is_ai_generated ?? false,
                $this->reply?->ai_provider,
                $this->reply?->ai_model,
                $this->reply?->tokens_used ?? 0
            );

            $this->success = 'تم حفظ المسودة';

        } catch (\Exception $e) {
            $this->error = 'فشل في الحفظ: ' . $e->getMessage();
        } finally {
            $this->isSaving = false;
        }
    }

    public function publishToGoogle(): void
    {
        if (empty(trim($this->replyText))) {
            $this->error = 'الرجاء كتابة رد أولاً';
            return;
        }

        if (!$this->canPublishToGoogle) {
            $this->error = 'لا يمكن النشر على Google. ' . ($this->connectionStatus ?? 'تحقق من اتصال Google.');
            return;
        }

        $this->isPublishing = true;
        $this->error = null;
        $this->success = null;

        try {
            // Save first
            $this->saveDraft();

            // Update status to publishing
            $this->reply->update(['status' => ReplyStatus::PUBLISHING]);

            // Publish to Google
            $googleService = app(GoogleReplyService::class);
            $result = $googleService->publishReply($this->review, $this->replyText);

            // Update reply status
            $this->reply->update([
                'status' => ReplyStatus::PUBLISHED,
                'published_at' => now(),
                'is_published' => true,
                'google_reply_id' => $result['reply_id'] ?? null,
                'error_message' => null,
            ]);

            // Update review
            $this->review->update([
                'is_replied' => true,
                'owner_reply' => $this->replyText,
                'owner_reply_date' => now(),
                'replied_via_tabsense' => true,
            ]);

            $this->success = 'تم نشر الرد على Google بنجاح!';

            // Refresh the reply model
            $this->reply = $this->reply->fresh();

            // Dispatch event to refresh the reviews table
            $this->dispatch('refreshReviews');

        } catch (\Exception $e) {
            $this->error = $e->getMessage();

            // Update status to failed
            $this->reply?->update([
                'status' => ReplyStatus::FAILED,
                'error_message' => $e->getMessage(),
            ]);
        } finally {
            $this->isPublishing = false;
        }
    }

    public function copyToClipboard(): void
    {
        $this->dispatch('copyToClipboard', text: $this->replyText);
        $this->success = 'تم نسخ الرد';
    }

    public function render()
    {
        return view('livewire.reply-modal', [
            'tones' => ReplyTone::cases(),
        ]);
    }
}
