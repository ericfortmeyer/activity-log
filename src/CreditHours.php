<?php

declare(strict_types=1);


namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\AbstractModel;
use Phpolar\Model\Hidden;
use Phpolar\Model\PrimaryKey;
use Phpolar\Validators\Max;
use Phpolar\Validators\Min;

final class CreditHours extends AbstractModel
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


    public function create(): void
    {
        $this->id = self::getIdFromMonth($this->year, $this->month);
    }

    public static function getIdFromMonth(int $year, int $month): string
    {
        return sprintf("%d-%02d", $year, $month);
    }
}
