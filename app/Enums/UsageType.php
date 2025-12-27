<?php

namespace App\Enums;

enum UsageType: string
{
    case AI_REPLY = 'ai_reply';
    case AI_TOKENS = 'ai_tokens';
    case API_CALL = 'api_call';
    case REVIEW_SYNC = 'review_sync';
    case ANALYSIS_RUN = 'analysis_run';

    public function label(): string
    {
        return match ($this) {
            self::AI_REPLY => 'ردود الذكاء الاصطناعي',
            self::AI_TOKENS => 'رموز AI',
            self::API_CALL => 'طلبات API',
            self::REVIEW_SYNC => 'مزامنة المراجعات',
            self::ANALYSIS_RUN => 'تشغيل التحليل',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::AI_REPLY => 'AI Replies',
            self::AI_TOKENS => 'AI Tokens',
            self::API_CALL => 'API Calls',
            self::REVIEW_SYNC => 'Reviews Synced',
            self::ANALYSIS_RUN => 'Analysis Runs',
        };
    }

    public function limitKey(): string
    {
        return match ($this) {
            self::AI_REPLY => 'max_ai_replies',
            self::AI_TOKENS => 'max_ai_tokens',
            self::API_CALL => 'max_api_calls',
            self::REVIEW_SYNC => 'max_reviews_sync',
            self::ANALYSIS_RUN => 'max_analysis_runs',
        };
    }

    public function summaryField(): string
    {
        return match ($this) {
            self::AI_REPLY => 'ai_replies_used',
            self::AI_TOKENS => 'ai_tokens_used',
            self::API_CALL => 'api_calls_used',
            self::REVIEW_SYNC => 'reviews_synced',
            self::ANALYSIS_RUN => 'analysis_runs',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AI_REPLY => 'heroicon-o-chat-bubble-left-right',
            self::AI_TOKENS => 'heroicon-o-sparkles',
            self::API_CALL => 'heroicon-o-code-bracket',
            self::REVIEW_SYNC => 'heroicon-o-arrow-path',
            self::ANALYSIS_RUN => 'heroicon-o-chart-bar',
        };
    }
}
