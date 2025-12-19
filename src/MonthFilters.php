<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use DateTimeImmutable;
use Phpolar\{
    Model\AbstractModel,
    Validators\Max,
    Validators\Min
};

/**
 * @phan-file-suppress PhanReadOnlyPublicProperty
 */
final class MonthFilters extends AbstractModel
{
    #[Max(2100)]
    #[Min(2010)]
    public int|null $filterYear = null;

    #[Max(12)]
    #[Min(1)]
    public int|null $filterMonth = null;

    public function hasFilter(): bool
    {
        return \is_null(
            $this->filterMonth,
        ) === false && \is_null(
            $this->filterYear,
        ) === false;
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
