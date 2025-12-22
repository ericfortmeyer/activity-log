<?php

namespace EricFortmeyer\ActivityLog;

use Phpolar\{
    Model\Hidden,
    Model\PrimaryKey,
    Validators\Max,
    Validators\MaxLength,
    Validators\Min
};
use Phpolar\Model\EntityName;

/**
 * @phan-file-suppress PhanReadOnlyPublicProperty
 */
#[EntityName("remarks")]
final class RemarksForMonth extends TenantData
{
    #[PrimaryKey]
    #[Hidden]
    public string $id;

    #[Min(1)]
    #[Max(12)]
    #[Hidden]
    public int $month;

    #[Min(2023)]
    #[Max(2026)]
    #[Hidden]
    public string $year;

    #[MaxLength(0x1000)]
    public string $remarks = "";

    public function create(string $tenantId): void
    {
        $this->id = self::getIdFromMonth($this->year, $this->month, $tenantId);
    }

    public static function getIdFromMonth(string $year, int $month, string $tenantId): string
    {
        return sprintf("%s-%d-%02d", $tenantId, $year, $month);
    }
}
