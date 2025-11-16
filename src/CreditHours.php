<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\{
    Hidden,
    PrimaryKey
};
use Phpolar\Phpolar\Auth\User;
use Phpolar\Validators\{
    Max,
    Min
};

final class CreditHours extends TenantData
{
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
    public int $year;


    public function create(User $user): void
    {
        parent::initForTenant($user);
        $this->id = self::getIdFromMonth($this->year, $this->month, $user->nickname);
    }

    public static function getIdFromMonth(int $year, int $month, string $tenantId): string
    {
        return sprintf("%s-%d-%02d", $tenantId, $year, $month);
    }
}
