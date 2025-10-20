<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use DateTimeImmutable;
use Phpolar\Model\AbstractModel;

use Phpolar\Validators\Max;

final class MonthFilters extends AbstractModel
{
    #[Max(2100)]
    public int $filterYear;

    #[Max(12)]
    public int $filterMonth;

    public function hasFilter(): bool
    {
        return isset(
            $this->filterMonth,
            $this->filterYear,
        );
    }

    public function getMonth(): int
    {
        return $this->filterMonth ?? (int) new DateTimeImmutable("now")->format("m");
    }

    public function getYear(): int
    {
        return $this->filterYear ?? (int) new DateTimeImmutable("now")->format("Y");
    }
}
