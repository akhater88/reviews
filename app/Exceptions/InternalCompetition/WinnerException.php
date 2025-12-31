<?php

namespace App\Exceptions\InternalCompetition;

use Exception;

class WinnerException extends Exception
{
    public static function competitionNotEnded(int $competitionId): self
    {
        return new self("لا يمكن تحديد الفائزين قبل انتهاء المسابقة: {$competitionId}");
    }

    public static function winnersAlreadyDetermined(int $competitionId): self
    {
        return new self("تم تحديد الفائزين مسبقاً للمسابقة: {$competitionId}");
    }

    public static function noPrizeForRank(string $metric, int $rank): self
    {
        return new self("لا توجد جائزة للمركز {$rank} في مقياس {$metric}");
    }

    public static function invalidStatusTransition(string $from, string $to): self
    {
        return new self("لا يمكن الانتقال من حالة {$from} إلى {$to}");
    }

    public static function winnerNotFound(int $winnerId): self
    {
        return new self("الفائز غير موجود: {$winnerId}");
    }

    public static function noScoresAvailable(int $competitionId): self
    {
        return new self("لا توجد نتائج متاحة للمسابقة: {$competitionId}");
    }

    public static function prizeAlreadyDelivered(int $winnerId): self
    {
        return new self("تم تسليم الجائزة مسبقاً للفائز: {$winnerId}");
    }
}
