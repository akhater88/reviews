<?php

namespace App\Exceptions\InternalCompetition;

use Exception;

class CompetitionException extends Exception
{
    public static function notFound(int $id): self
    {
        return new self("المسابقة غير موجودة: {$id}");
    }

    public static function cannotModify(string $status): self
    {
        return new self("لا يمكن تعديل المسابقة في حالة: {$status}");
    }

    public static function invalidDateRange(): self
    {
        return new self("تاريخ البداية يجب أن يكون قبل تاريخ النهاية");
    }

    public static function startDateInPast(): self
    {
        return new self("تاريخ البداية يجب أن يكون في المستقبل");
    }

    public static function noMetricsEnabled(): self
    {
        return new self("يجب تفعيل مقياس واحد على الأقل");
    }

    public static function unauthorized(): self
    {
        return new self("ليس لديك صلاحية لهذا الإجراء");
    }
}
