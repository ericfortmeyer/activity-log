<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\{
    EntityName,
    Hidden,
    PrimaryKey
};
use Phpolar\Validators\{
    Max,
    Min
};

#[EntityName("credit-hours")]
final class CreditHours extends TenantData
{
    /**
     * @suppress PhanUnreferencedPublicProperty
     */
    #[Hidden]
    #[PrimaryKey]
    public string $id;

    #[Max(2000)]
    #[Min(0)]
    public int $hours = 0;

    #[Hidden]
    #[Min(1)]
    #[Max(12)]
    public int $month;

    #[Hidden]
    #[Min(1900)]
    #[Max(3000)]
    public string $year;


    public function create(string $tenantId): void
    {
        $this->tenantId = $tenantId;
        $this->id = self::getIdFromMonth($this->year, $this->month, $tenantId);
    }

    public static function getIdFromMonth(string $year, int $month, string $tenantId): string
    {
        return sprintf("%s-%d-%02d", $tenantId, $year, $month);
    }
}
