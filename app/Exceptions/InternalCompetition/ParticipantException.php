<?php

namespace App\Exceptions\InternalCompetition;

use Exception;

class ParticipantException extends Exception
{
    public static function tenantAlreadyEnrolled(int $tenantId): self
    {
        return new self("المستأجر مسجل بالفعل في المسابقة: {$tenantId}");
    }

    public static function branchAlreadyEnrolled(int $branchId): self
    {
        return new self("الفرع مسجل بالفعل في المسابقة: {$branchId}");
    }

    public static function tenantNotEnrolled(int $tenantId): self
    {
        return new self("المستأجر غير مسجل في المسابقة: {$tenantId}");
    }

    public static function branchNotEnrolled(int $branchId): self
    {
        return new self("الفرع غير مسجل في المسابقة: {$branchId}");
    }

    public static function cannotEnrollAfterStart(): self
    {
        return new self("لا يمكن التسجيل بعد بدء المسابقة");
    }

    public static function cannotWithdrawAfterEnd(): self
    {
        return new self("لا يمكن الانسحاب بعد انتهاء المسابقة");
    }

    public static function branchNotBelongsToTenant(int $branchId, int $tenantId): self
    {
        return new self("الفرع {$branchId} لا ينتمي للمستأجر {$tenantId}");
    }

    public static function tenantNotInCompetition(int $tenantId): self
    {
        return new self("المستأجر {$tenantId} غير مشارك في هذه المسابقة");
    }

    public static function alreadyWithdrawn(): self
    {
        return new self("تم الانسحاب مسبقاً");
    }
}
