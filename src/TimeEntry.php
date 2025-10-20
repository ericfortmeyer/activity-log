<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use DateTimeImmutable;
use Phpolar\Model\AbstractModel;
use Phpolar\Model\Hidden;
use Phpolar\Model\Label;
use Phpolar\Model\PrimaryKey;
use Phpolar\Validators\Max;
use Phpolar\Validators\MaxLength;
use Phpolar\Validators\Min;

final class TimeEntry extends AbstractModel
{
    #[MaxLength(20)]
    #[PrimaryKey]
    #[Hidden]
    public string $id;

    #[Min(1)]
    #[Max(31)]
    #[Label("Day of Month")]
    public int $dayOfMonth;

    #[Min(1)]
    #[Max(12)]
    public int $month;

    #[Min(2025)]
    #[Max(2026)]
    public int $year;

    #[Max(24)]
    #[Min(0)]
    public int $hours;

    #[Min(0)]
    #[Max(59)]
    public int $minutes;

    #[Hidden]
    public DateTimeImmutable $createdOn;

    public function create(): void
    {
        $this->id = uniqid();
        $this->createdOn = new DateTimeImmutable("now");
    }

    public function getDate(): string
    {
        return sprintf(
            "%02d/%02d/%04d",
            $this->month,
            $this->dayOfMonth,
            $this->year,
        );
    }

    public function getDuration(): string
    {
        return sprintf(
            "%02dh %02dm",
            $this->hours,
            $this->minutes,
        );
    }

    public static function fromData(array | object $data): self
    {
        return new TimeEntry($data);
    }

    public static function getDefaultValue(string $field): mixed
    {
        $date = new DateTimeImmutable("now");
        return match ($field) {
            "dayOfMonth" => (int)$date->format("j"),
            "month" => (int)$date->format("m"),
            "year" => (int)$date->format("Y"),
            "hours" => 0,
            "minutes" => 0,
            default => null,
        };
    }
}
