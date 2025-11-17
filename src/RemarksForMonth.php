<?php

namespace EricFortmeyer\ActivityLog;

use Phpolar\Phpolar\Auth\User;
use Phpolar\{
    Model\Hidden,
    Model\PrimaryKey,
    Validators\Max,
    Validators\MaxLength,
    Validators\Min
};

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
    public int $year;

    #[MaxLength(2100)]
    public string $remarks = "";

    public function create(string $tenantId): void
    {
        $this->tenantId = $tenantId;
        $this->id = self::getIdFromMonth($this->year, $this->month, $tenantId);
    }

    public static function getIdFromMonth(int $year, int $month, string $tenantId): string
    {
        return sprintf("%s-%d-%02d", $tenantId, $year, $month);
    }

    /**
     * @param array<string|int,string|int>|object $data
     */
    public static function fromData(array | object $data): self
    {
        return new self($data);
    }
}
