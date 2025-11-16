<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Closure;
use DateTimeImmutable;
use Phpolar\Model\{
    Hidden,
    Label,
    PrimaryKey
};
use Phpolar\Phpolar\Auth\User;
use Phpolar\Validators\{
    Max,
    MaxLength,
    Min,
    Required,
};

class TimeEntry extends TenantData
{
    #[MaxLength(20)]
    #[PrimaryKey]
    #[Required]
    #[Hidden]
    public string $id;

    #[Min(1)]
    #[Max(31)]
    #[Required]
    #[Label("Day of Month")]
    public int $dayOfMonth;

    #[Min(1)]
    #[Max(12)]
    #[Required]
    public int $month;

    #[Min(2025)]
    #[Max(2026)]
    #[Required]
    public int $year;

    #[Max(24)]
    #[Min(0)]
    #[Required]
    public int $hours;

    #[Min(0)]
    #[Max(59)]
    public int $minutes;

    #[Hidden]
    public DateTimeImmutable $createdOn;

    public function create(User $user): void
    {
        parent::initForTenant($user);
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
            "%dh %dm",
            $this->hours,
            $this->minutes,
        );
    }

    /**
     * @param array<int|string,int|string>|object $data
     */
    public static function fromData(array | object $data): self
    {
        return new TimeEntry($data);
    }

    public static function getDefaultValue(string $field, DateTimeImmutable $date = new DateTimeImmutable("now")): mixed
    {
        return match ($field) {
            "dayOfMonth" => (int)$date->format("j"),
            "month" => (int)$date->format("m"),
            "year" => (int)$date->format("Y"),
            "hours" => 0,
            "minutes" => 0,
            default => null,
        };
    }

    public static function forTenant(string $tenantId): Closure
    {
        return static fn(TimeEntry $timeEntry): bool => $timeEntry->tenantId === $tenantId;
    }

    public static function byMonthAndYear(int $month, int $year): Closure
    {
        return static fn(TimeEntry $timeEntry): bool => $timeEntry->month === $month && $timeEntry->year === $year;
    }
}
