<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UserInterface\Contexts;

use DateTimeImmutable;
use EricFortmeyer\ActivityLog\CreditHours;
use EricFortmeyer\ActivityLog\EmailReport;
use EricFortmeyer\ActivityLog\MonthFilters;
use EricFortmeyer\ActivityLog\RemarksForMonth;
use EricFortmeyer\ActivityLog\TimeEntry;
use Phpolar\Storage\NotFound;

final class EmailReportContext extends AbstractContext
{
    /**
     * @param TimeEntry[] $timeEntries
     */
    public function __construct(
        public EmailReport $emailReport,
        public array $timeEntries,
        public TimeEntry $currentEntry,
        public MonthFilters $filters,
        public RemarksForMonth|NotFound $remarks,
        public CreditHours|NotFound $creditHours,
    ) {
        parent::__construct(
            title: "Monthly Activity Report"
        );
    }

    public function shouldShowCreditHours(): bool
    {
        return $this->creditHours instanceof CreditHours && $this->creditHours->hours > 0;
    }

    public function getCreditHours(): int
    {
        return $this->creditHours instanceof NotFound ? 0 : $this->creditHours->hours;
    }

    public function getCreditHoursText(): string
    {
        return sprintf(
            "Hour%s",
            $this->getCreditHours() !== 1
                ? "s"
                : ""
        );
    }

    public function getTotalHours(): int
    {
        return array_reduce(
            $this->timeEntries,
            static fn(int $acc, TimeEntry $entry): int => $acc + $entry->hours,
            $this->getHoursFromTotalMinutes(),
        );
    }

    private function getHoursFromTotalMinutes(): int
    {
        return (int) floor(array_reduce(
            $this->timeEntries,
            static fn(int $acc, TimeEntry $entry): int => $acc + $entry->minutes,
            0,
        ) / 60);
    }
    public function getRemarksForCurrentMonth(): string
    {
        return $this->remarks instanceof NotFound ? "" : $this->remarks->remarks;
    }

    public function getHoursText(): string
    {
        return sprintf("Hour%s", $this->getTotalHours() !== 1 ? "s" : "");
    }

    public function hasRemarks(): bool
    {
        return $this->remarks instanceof RemarksForMonth && $this->remarks->remarks !== "";
    }

    public function getMonthTitle(): string
    {
        $month = $this->emailReport->month;
        $year = $this->emailReport->year;
        $month = DateTimeImmutable::createFromFormat("!m", (string)$month);
        return sprintf("%s %d", $month ? $month->format("F") : "", $year);
    }
}
