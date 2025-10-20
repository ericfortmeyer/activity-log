<?php

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\AbstractModel;
use Phpolar\Model\Hidden;
use Phpolar\Model\PrimaryKey;
use Phpolar\Validators\Max;
use Phpolar\Validators\MaxLength;
use Phpolar\Validators\Min;

final class RemarksForMonth extends AbstractModel
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

    public function create(): void
    {
        $this->id = self::getIdFromMonth($this->year, $this->month);
    }

    public static function getIdFromMonth(int $year, int $month): string
    {
        return sprintf("%d-%02d", $year, $month);
    }

    public static function fromData(array | object $data): self
    {
        return new self($data);
    }
}
