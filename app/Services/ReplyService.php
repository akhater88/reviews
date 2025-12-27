<?php

namespace App\Services;

use App\Enums\ReplyStatus;
use App\Enums\ReplyTone;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Services\AI\AIServiceFactory;
use App\Services\AI\AIServiceInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class ReplyService
{
    protected AIServiceInterface $aiService;

    public function __construct()
    {
        $this->aiService = AIServiceFactory::make();
    }

    /**
     * Generate AI reply for a review.
     */
    public function generateReply(
        Review $review,
        ReplyTone $tone = ReplyTone::PROFESSIONAL,
        ?string $businessName = null
    ): array {
        $prompt = $this->buildPrompt($review, $tone, $businessName);

        try {
            $result = $this->aiService->complete($prompt, [
                'system_message' => $this->getSystemMessage($tone),
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            $replyText = $this->extractReplyText($result['content']);

            Log::info('AI Reply Generated', [
                'review_id' => $review->id,
                'tone' => $tone->value,
                'provider' => $result['provider'],
            ]);

            return [
                'reply_text' => $replyText,
                'provider' => $result['provider'],
                'model' => $result['model'],
                'tokens_used' => $result['usage']['total_tokens'] ?? 0,
            ];

        } catch (Exception $e) {
            Log::error('AI Reply Generation Failed', [
                'review_id' => $review->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Extract reply text from AI response.
     */
    private function extractReplyText(mixed $content): string
    {
        if (is_string($content)) {
            // Try to parse as JSON
            $decoded = json_decode($content, true);
            if (isset($decoded['reply'])) {
                return $decoded['reply'];
            }
            return $content;
        }

        if (is_array($content)) {
            return $content['reply'] ?? $content['response'] ?? json_encode($content);
        }

        return (string) $content;
    }

    /**
     * Build prompt based on review and tone.
     */
    private function buildPrompt(Review $review, ReplyTone $tone, ?string $businessName): string
    {
        $businessContext = $businessName ?? $review->branch?->name ?? 'مطعمنا';
        $rating = $review->rating ?? 3;
        $reviewText = $review->text ?? 'تقييم بالنجوم فقط';
        $reviewerName = $review->reviewer_name ?? 'العميل الكريم';
        $sentiment = $review->sentiment ?? 'neutral';

        $toneInstructions = match($tone) {
            ReplyTone::FRIENDLY => <<<TONE
استخدم لهجة ودية ودافئة. يمكنك استخدام إيموجي واحد أو اثنين مناسبين (مثل 😊 أو ❤️ أو 🙏).
كن شخصياً ومرحباً. عبر عن سعادتك بتجربة العميل الإيجابية.
TONE,
            ReplyTone::APOLOGETIC => <<<TONE
استخدم لهجة اعتذارية ومتفهمة. اعترف بالمشكلة بوضوح.
أظهر تعاطفاً حقيقياً واعرض حلاً ملموساً أو دعوة للتواصل المباشر.
لا تستخدم إيموجي. كن جاداً ومهنياً في الاعتذار.
TONE,
            default => <<<TONE
استخدم لهجة مهنية ومحترمة. كن رسمياً لكن ودوداً.
لا تستخدم إيموجي. حافظ على احترافية الرد.
TONE,
        };

        $ratingGuidelines = match(true) {
            $rating >= 4 => <<<GUIDE
هذا تقييم إيجابي ({$rating} نجوم).
- اشكر العميل بحرارة على كلماته الطيبة
- أكد على سعادتك بتجربته الإيجابية
- ادعه لزيارة أخرى قريباً
GUIDE,
            $rating <= 2 => <<<GUIDE
هذا تقييم سلبي ({$rating} نجوم).
- ابدأ بالاعتذار الصادق عن التجربة السيئة
- اعترف بالمشكلة المذكورة (إن وجدت)
- اعرض التواصل المباشر لحل المشكلة
- وعد بالتحسين
GUIDE,
            default => <<<GUIDE
هذا تقييم متوسط ({$rating} نجوم).
- اشكر العميل على ملاحظاته القيمة
- أكد على التزامك بالتحسين المستمر
- ادعه لتجربة أخرى
GUIDE,
        };

        return <<<PROMPT
أنت مدير خدمة عملاء في "{$businessContext}". اكتب رداً على المراجعة التالية.

═══════════════════════════════════════
معلومات المراجعة:
═══════════════════════════════════════
- اسم العميل: {$reviewerName}
- التقييم: {$rating} من 5 نجوم
- المشاعر: {$sentiment}
- نص المراجعة: "{$reviewText}"

═══════════════════════════════════════
تعليمات اللهجة:
═══════════════════════════════════════
{$toneInstructions}

═══════════════════════════════════════
إرشادات بناءً على التقييم:
═══════════════════════════════════════
{$ratingGuidelines}

═══════════════════════════════════════
قواعد عامة للرد:
═══════════════════════════════════════
1. ابدأ بمخاطبة العميل باسمه إن وجد (مثال: "عزيزنا أحمد" أو "أخي الكريم")
2. الرد يجب أن يكون 2-4 جمل فقط (50-150 كلمة)
3. لا تكرر نفس العبارات في كل رد
4. اجعل الرد شخصياً ومرتبطاً بمحتوى المراجعة
5. اختم بدعوة مناسبة (زيارة أخرى أو تواصل)
6. لا تستخدم عبارات روبوتية أو مكررة
7. اكتب بالعربية الفصحى السهلة

أرجع الرد بتنسيق JSON:
{
  "reply": "نص الرد هنا"
}
PROMPT;
    }

    /**
     * Get system message based on tone.
     */
    private function getSystemMessage(ReplyTone $tone): string
    {
        $baseMessage = 'أنت مدير خدمة عملاء محترف في مطعم سعودي. تكتب ردوداً على مراجعات العملاء بالعربية.';

        return match($tone) {
            ReplyTone::FRIENDLY => $baseMessage . ' أسلوبك ودود ودافئ ومرح. تستخدم إيموجي بشكل مناسب.',
            ReplyTone::APOLOGETIC => $baseMessage . ' أسلوبك متفهم ومعتذر. تظهر اهتماماً حقيقياً بحل المشاكل.',
            default => $baseMessage . ' أسلوبك مهني ومحترم. ردودك رسمية لكن ودودة.',
        };
    }

    /**
     * Save reply to database.
     */
    public function saveReply(
        Review $review,
        string $replyText,
        ReplyTone $tone,
        bool $isAiGenerated = false,
        ?string $provider = null,
        ?string $model = null,
        int $tokensUsed = 0
    ): ReviewReply {
        return ReviewReply::updateOrCreate(
            ['review_id' => $review->id],
            [
                'reply_text' => $replyText,
                'ai_tone' => $tone->value,
                'is_ai_generated' => $isAiGenerated,
                'ai_provider' => $provider,
                'ai_model' => $model,
                'tokens_used' => $tokensUsed,
                'status' => ReplyStatus::DRAFT,
                'user_id' => auth()->id(),
            ]
        );
    }

    /**
     * Update existing reply.
     */
    public function updateReply(ReviewReply $reply, string $replyText): ReviewReply
    {
        $reply->update([
            'reply_text' => $replyText,
            'status' => ReplyStatus::DRAFT,
        ]);

        return $reply->fresh();
    }
}
