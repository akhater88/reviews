<?php

namespace App\Filament\SuperAdmin\Resources\TenantResource\Widgets;

use App\Models\UsageSummary;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class TenantUsageWidget extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $tenant = $this->record;
        $usage = UsageSummary::getCurrentForTenant($tenant->id);
        $limits = $tenant->currentSubscription?->plan?->limits;

        // Calculate percentages
        $aiRepliesPercent = $limits?->max_ai_replies > 0 && $limits->max_ai_replies !== -1
            ? round(($usage->ai_replies_used / $limits->max_ai_replies) * 100)
            : 0;

        $branchesPercent = $limits?->max_branches > 0 && $limits->max_branches !== -1
            ? round(($tenant->branches()->where('is_competitor', false)->count() / $limits->max_branches) * 100)
            : 0;

        $competitorsPercent = $limits?->max_competitors > 0 && $limits->max_competitors !== -1
            ? round(($tenant->branches()->where('is_competitor', true)->count() / $limits->max_competitors) * 100)
            : 0;

        return [
            Stat::make('ردود AI المستخدمة', $usage->ai_replies_used)
                ->description($limits?->max_ai_replies === -1
                    ? 'غير محدود'
                    : "من {$limits?->max_ai_replies} ({$aiRepliesPercent}%)"
                )
                ->color($aiRepliesPercent >= 80 ? 'danger' : ($aiRepliesPercent >= 50 ? 'warning' : 'success'))
                ->icon('heroicon-o-sparkles'),

            Stat::make('الفروع', $tenant->branches()->where('is_competitor', false)->count())
                ->description($limits?->max_branches === -1
                    ? 'غير محدود'
                    : "من {$limits?->max_branches} ({$branchesPercent}%)"
                )
                ->color($branchesPercent >= 80 ? 'warning' : 'success')
                ->icon('heroicon-o-building-office'),

            Stat::make('المنافسين', $tenant->branches()->where('is_competitor', true)->count())
                ->description($limits?->max_competitors === -1
                    ? 'غير محدود'
                    : "من {$limits?->max_competitors} ({$competitorsPercent}%)"
                )
                ->color($competitorsPercent >= 80 ? 'warning' : 'success')
                ->icon('heroicon-o-flag'),

            Stat::make('المراجعات المزامنة', number_format($usage->reviews_synced))
                ->description($limits?->max_reviews_sync === -1
                    ? 'غير محدود'
                    : "من {$limits?->max_reviews_sync}"
                )
                ->icon('heroicon-o-chat-bubble-left-right'),
        ];
    }
}
