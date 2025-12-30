<?php

namespace App\Exceptions\InternalCompetition;

use Exception;

class InsufficientParticipantsException extends Exception
{
    public static function noTenants(): self
    {
        return new self("يجب إضافة مستأجر واحد على الأقل للمسابقة متعددة المستأجرين");
    }

    public static function noBranches(): self
    {
        return new self("يجب إضافة فرع واحد على الأقل للمسابقة");
    }

    public static function minimumBranches(int $required, int $current): self
    {
        return new self("يجب إضافة {$required} فروع على الأقل. الحالي: {$current}");
    }

    public static function minimumTenants(int $required, int $current): self
    {
        return new self("يجب إضافة {$required} مستأجرين على الأقل. الحالي: {$current}");
    }
}
