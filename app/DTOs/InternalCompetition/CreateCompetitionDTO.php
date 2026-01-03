<?php

namespace App\DTOs\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionScope;
use App\Enums\InternalCompetition\CompetitionPeriod;
use App\Enums\InternalCompetition\LeaderboardVisibility;
use Carbon\Carbon;

class CreateCompetitionDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $nameAr,
        public readonly ?string $description,
        public readonly ?string $descriptionAr,
        public readonly CompetitionScope $scope,
        public readonly CompetitionPeriod $periodType,
        public readonly Carbon $startDate,
        public readonly Carbon $endDate,
        public readonly array $metricsConfig,
        public readonly LeaderboardVisibility $leaderboardVisibility,
        public readonly bool $showProgressHints,
        public readonly bool $publicShowcase,
        public readonly ?array $notificationSettings,
        public readonly int $createdById,
        public readonly string $createdByType,
        public readonly ?int $tenantId = null,
        public readonly ?string $coverImage = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            nameAr: $data['name_ar'] ?? null,
            description: $data['description'] ?? null,
            descriptionAr: $data['description_ar'] ?? null,
            scope: $data['scope'] instanceof CompetitionScope
                ? $data['scope']
                : CompetitionScope::from($data['scope']),
            periodType: $data['period_type'] instanceof CompetitionPeriod
                ? $data['period_type']
                : CompetitionPeriod::from($data['period_type']),
            startDate: $data['start_date'] instanceof Carbon
                ? $data['start_date']
                : Carbon::parse($data['start_date']),
            endDate: $data['end_date'] instanceof Carbon
                ? $data['end_date']
                : Carbon::parse($data['end_date']),
            metricsConfig: $data['metrics_config'],
            leaderboardVisibility: $data['leaderboard_visibility'] instanceof LeaderboardVisibility
                ? $data['leaderboard_visibility']
                : LeaderboardVisibility::from($data['leaderboard_visibility']),
            showProgressHints: $data['show_progress_hints'] ?? false,
            publicShowcase: $data['public_showcase'] ?? false,
            notificationSettings: $data['notification_settings'] ?? null,
            createdById: $data['created_by_id'],
            createdByType: $data['created_by_type'],
            tenantId: $data['tenant_id'] ?? null,
            coverImage: $data['cover_image'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'name_ar' => $this->nameAr,
            'description' => $this->description,
            'description_ar' => $this->descriptionAr,
            'scope' => $this->scope->value,
            'period_type' => $this->periodType->value,
            'start_date' => $this->startDate->toDateString(),
            'end_date' => $this->endDate->toDateString(),
            'metrics_config' => $this->metricsConfig,
            'leaderboard_visibility' => $this->leaderboardVisibility->value,
            'show_progress_hints' => $this->showProgressHints,
            'public_showcase' => $this->publicShowcase,
            'notification_settings' => $this->notificationSettings,
            'created_by_id' => $this->createdById,
            'created_by_type' => $this->createdByType,
            'tenant_id' => $this->tenantId,
            'cover_image' => $this->coverImage,
        ];
    }

    /**
     * Get default metrics configuration
     */
    public static function defaultMetricsConfig(): array
    {
        return [
            'employee_mentions' => [
                'enabled' => true,
                'weight' => 1.0,
            ],
            'customer_satisfaction' => [
                'enabled' => true,
                'weight' => 1.0,
            ],
            'response_time' => [
                'enabled' => true,
                'weight' => 1.0,
            ],
            'food_taste' => [
                'enabled' => true,
                'weight' => 1.0,
            ],
        ];
    }

    /**
     * Get default notification settings
     */
    public static function defaultNotificationSettings(): array
    {
        return [
            'whatsapp' => [
                'enabled' => true,
                'events' => ['start', 'reminder', 'ending_soon', 'ended', 'winner'],
            ],
            'email' => [
                'enabled' => true,
                'events' => ['start', 'reminder', 'ending_soon', 'ended', 'winner'],
            ],
        ];
    }
}
