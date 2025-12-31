<?php

namespace App\Exceptions\InternalCompetition;

use Exception;

class EmployeeExtractionException extends Exception
{
    public static function noAnalysisFound(int $branchId): self
    {
        return new self("لم يتم العثور على تحليل للموظفين للفرع: {$branchId}");
    }

    public static function invalidAnalysisFormat(int $analysisId): self
    {
        return new self("تنسيق تحليل غير صالح: {$analysisId}");
    }

    public static function extractionFailed(string $reason): self
    {
        return new self("فشل استخراج بيانات الموظفين: {$reason}");
    }

    public static function noEmployeesFound(int $branchId): self
    {
        return new self("لم يتم العثور على موظفين مذكورين للفرع: {$branchId}");
    }
}
