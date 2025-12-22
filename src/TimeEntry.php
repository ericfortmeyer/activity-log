<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Closure;
use DateTimeImmutable;
use Phpolar\Model\{
    EntityName,
    Hidden,
    Label,
    PrimaryKey
};
use Phpolar\Validators\{
    Max,
    MaxLength,
    Min,
    Required,
};

/**
 * @phan-file-suppress PhanReadOnlyPublicProperty
 */
#[EntityName("time-entry")]
class TimeEntry extends TenantData
{
    #[MaxLength(20)]
    #[PrimaryKey]
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
    public string $year;

    #[Max(24)]
    #[Min(0)]
    #[Required]
    public int $hours;

    #[Max(59)]
    #[Min(0)]
    public int $minutes;

    #[Hidden]
    // phpcs:disable
    public DateTimeImmutable $createdOn {
        set(string | DateTimeImmutable $value) {
            if (is_string($value) === true) {
                $value = new DateTimeImmutable($value);
            }
            $value = $value;
        }
        get => $this->createdOn ?? new DateTimeImmutable("now");
    }
    // phpcs:enable

    public function create(): void
    {
        $this->id = uniqid();
    }

    public static function setUninitializedValues(TimeEntry $timeEntry, int $month, string $year): void
    {
        $timeEntry->month ??= $month;
        $timeEntry->year ??= $year;
        $timeEntry->dayOfMonth ??= TimeEntry::getDefaultValue("dayOfMonth");
        $timeEntry->hours ??= 0;
        $timeEntry->minutes ??= 0;
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

    public function isValid(): bool
    {
        return parent::isValid() && ($this->minutes === 0 && $this->hours === 0) === false;
    }

    public function hasHoursError(): bool
    {
        return $this->shouldValidate === true
            && ($this->minutes === 0 && $this->hours === 0)
            || $this->hasError("hours");
    }

    public function getHoursErrorMessage(): string
    {
        return ($this->minutes === 0 && $this->hours === 0)
            ? "Either minutes or hours should be entered. ⚠"
            : $this->getFieldErrorMessage("hours", " ⚠");
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

    /**
     * @suppress PhanUnreferencedClosure
     */
    public static function forTenant(string $tenantId): Closure
    {
        return static fn(TimeEntry $timeEntry): bool => $timeEntry->tenantId === $tenantId;
    }

    /**
     * @suppress PhanUnreferencedClosure
     */
    public static function byMonthAndYear(int $month, string $year): Closure
    {
        return static fn(TimeEntry $timeEntry): bool =>
        $timeEntry->month === $month
            && $timeEntry->year === $year;
    }
}
