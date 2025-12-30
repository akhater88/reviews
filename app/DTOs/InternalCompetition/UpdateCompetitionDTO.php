<?php

namespace App\DTOs\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionPeriod;
use App\Enums\InternalCompetition\LeaderboardVisibility;
use Carbon\Carbon;

class UpdateCompetitionDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $nameAr = null,
        public readonly ?string $description = null,
        public readonly ?string $descriptionAr = null,
        public readonly ?CompetitionPeriod $periodType = null,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $endDate = null,
        public readonly ?array $metricsConfig = null,
        public readonly ?LeaderboardVisibility $leaderboardVisibility = null,
        public readonly ?bool $showProgressHints = null,
        public readonly ?bool $publicShowcase = null,
        public readonly ?array $notificationSettings = null,
        public readonly ?string $coverImage = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            nameAr: $data['name_ar'] ?? null,
            description: $data['description'] ?? null,
            descriptionAr: $data['description_ar'] ?? null,
            periodType: isset($data['period_type'])
                ? ($data['period_type'] instanceof CompetitionPeriod
                    ? $data['period_type']
                    : CompetitionPeriod::from($data['period_type']))
                : null,
            startDate: isset($data['start_date'])
                ? ($data['start_date'] instanceof Carbon
                    ? $data['start_date']
                    : Carbon::parse($data['start_date']))
                : null,
            endDate: isset($data['end_date'])
                ? ($data['end_date'] instanceof Carbon
                    ? $data['end_date']
                    : Carbon::parse($data['end_date']))
                : null,
            metricsConfig: $data['metrics_config'] ?? null,
            leaderboardVisibility: isset($data['leaderboard_visibility'])
                ? ($data['leaderboard_visibility'] instanceof LeaderboardVisibility
                    ? $data['leaderboard_visibility']
                    : LeaderboardVisibility::from($data['leaderboard_visibility']))
                : null,
            showProgressHints: $data['show_progress_hints'] ?? null,
            publicShowcase: $data['public_showcase'] ?? null,
            notificationSettings: $data['notification_settings'] ?? null,
            coverImage: $data['cover_image'] ?? null,
        );
    }

    /**
     * Get only the fields that have been set (not null)
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) $data['name'] = $this->name;
        if ($this->nameAr !== null) $data['name_ar'] = $this->nameAr;
        if ($this->description !== null) $data['description'] = $this->description;
        if ($this->descriptionAr !== null) $data['description_ar'] = $this->descriptionAr;
        if ($this->periodType !== null) $data['period_type'] = $this->periodType->value;
        if ($this->startDate !== null) $data['start_date'] = $this->startDate->toDateString();
        if ($this->endDate !== null) $data['end_date'] = $this->endDate->toDateString();
        if ($this->metricsConfig !== null) $data['metrics_config'] = $this->metricsConfig;
        if ($this->leaderboardVisibility !== null) $data['leaderboard_visibility'] = $this->leaderboardVisibility->value;
        if ($this->showProgressHints !== null) $data['show_progress_hints'] = $this->showProgressHints;
        if ($this->publicShowcase !== null) $data['public_showcase'] = $this->publicShowcase;
        if ($this->notificationSettings !== null) $data['notification_settings'] = $this->notificationSettings;
        if ($this->coverImage !== null) $data['cover_image'] = $this->coverImage;

        return $data;
    }

    public function hasChanges(): bool
    {
        return !empty($this->toArray());
    }
}
