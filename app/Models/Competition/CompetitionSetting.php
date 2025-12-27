<?php

namespace App\Models\Competition;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CompetitionSetting extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'is_public' => 'boolean',
        'updated_at' => 'datetime',
    ];

    protected const CACHE_PREFIX = 'competition_setting:';

    protected const CACHE_TTL = 3600;

    public static function get(string $key, $default = null)
    {
        return Cache::remember(self::CACHE_PREFIX . $key, self::CACHE_TTL, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            return $setting ? self::castValue($setting->value, $setting->type) : $default;
        });
    }

    public static function set(string $key, $value, ?int $updatedBy = null): bool
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return false;
        }

        $setting->update([
            'value' => is_array($value) ? json_encode($value) : (string) $value,
            'updated_at' => now(),
            'updated_by' => $updatedBy,
        ]);

        Cache::forget(self::CACHE_PREFIX . $key);

        return true;
    }

    public static function getPublicSettings(): array
    {
        return self::where('is_public', true)
            ->get()
            ->mapWithKeys(fn ($s) => [$s->key => self::castValue($s->value, $s->type)])
            ->toArray();
    }

    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    public static function isEnabled(): bool
    {
        return self::get('is_enabled', true);
    }

    public static function getWinnerCount(): int
    {
        return self::get('winner_count', 10);
    }

    public static function getMinReviews(): int
    {
        return self::get('min_reviews', 10);
    }

    public static function getScoreWeights(): array
    {
        return [
            'rating' => self::get('score_weight_rating', 25),
            'sentiment' => self::get('score_weight_sentiment', 30),
            'response_rate' => self::get('score_weight_response', 15),
            'volume' => self::get('score_weight_volume', 10),
            'trend' => self::get('score_weight_trend', 10),
            'keywords' => self::get('score_weight_keywords', 10),
        ];
    }

    public static function getPrizes(): array
    {
        return [
            1 => self::get('prize_1', 2000),
            2 => self::get('prize_2', 1500),
            3 => self::get('prize_3', 1000),
            'others' => self::get('prize_others', 500),
        ];
    }
}
