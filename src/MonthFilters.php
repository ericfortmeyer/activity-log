<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use DateTimeImmutable;
use Phpolar\{
    Model\AbstractModel,
    Validators\Max,
    Validators\Min
};

final class MonthFilters extends AbstractModel
{
    #[Max(2100)]
    #[Min(2010)]
    public int $filterYear;

    #[Max(12)]
    #[Min(1)]
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
