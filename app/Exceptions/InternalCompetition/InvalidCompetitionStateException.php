<?php

namespace App\Exceptions\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionStatus;
use Exception;

class InvalidCompetitionStateException extends Exception
{
    public static function cannotActivate(CompetitionStatus $currentStatus): self
    {
        return new self(
            "لا يمكن تفعيل المسابقة من حالة: {$currentStatus->getLabel()}"
        );
    }

    public static function cannotEnd(CompetitionStatus $currentStatus): self
    {
        return new self(
            "لا يمكن إنهاء المسابقة من حالة: {$currentStatus->getLabel()}"
        );
    }

    public static function cannotPublish(CompetitionStatus $currentStatus): self
    {
        return new self(
            "لا يمكن نشر النتائج من حالة: {$currentStatus->getLabel()}"
        );
    }

    public static function cannotCancel(CompetitionStatus $currentStatus): self
    {
        return new self(
            "لا يمكن إلغاء المسابقة من حالة: {$currentStatus->getLabel()}"
        );
    }

    public static function alreadyInState(CompetitionStatus $status): self
    {
        return new self(
            "المسابقة بالفعل في حالة: {$status->getLabel()}"
        );
    }
}
