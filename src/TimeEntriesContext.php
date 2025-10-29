<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use DateTimeImmutable;

/**
 * Context for the time entries list view
 */
final class TimeEntriesContext
{
    public function __construct(
        /**
         * @param TimeEntry[] $timeEntries
         */
        public array $timeEntries = [],
        public TimeEntry $currentEntry = new TimeEntry(),
        public MonthFilters $filters = new MonthFilters(),
        public RemarksForMonth $remarks = new RemarksForMonth(),
        public CreditHours $creditHours = new CreditHours(),
        private readonly string $title = "Activity Log",
        private readonly string $addActivityFormTitle = "Add an activity",
        private readonly string $addActivityInstructions = "Select a date. Then enter the hours and minutes "
            . "for the duration of the activity.",
        private readonly string $deleteUrl = "/time-entry/delete",
        private readonly string $emailUrl = "/report/send",
    ) {}

    public function getTitle(): string
    {
        return $this->title;
    }

    public function canShowReport(): bool
    {
        return count($this->timeEntries) > 0;
    }

    public function shouldShowCreditHours(): bool
    {
        return $this->creditHours->hours > 0;
    }

    public function getCreditHours(): int
    {
        return $this->creditHours->hours;
    }

    public function getTotalHours(): int
    {
        return array_reduce(
            $this->timeEntries,
            static fn(int $acc, TimeEntry $entry): int => $acc + $entry->hours,
            $this->getHoursFromTotalMinutes(),
        );
    }

    public function getMinutesRemainder(): int
    {
        return array_reduce(
            $this->timeEntries,
            static fn(int $acc, TimeEntry $entry): int => $acc + $entry->minutes,
            0
        ) % 60;
    }

    private function getHoursFromTotalMinutes(): int
    {
        return (int) floor(array_reduce(
            $this->timeEntries,
            static fn(int $acc, TimeEntry $entry): int => $acc + $entry->minutes,
            0,
        ) / 60);
    }

    public function getAddActivityInstructions(): string
    {
        return $this->addActivityInstructions;
    }

    public function getAddActivityFormTitle(): string
    {
        return $this->addActivityFormTitle;
    }

    public function getNextMonthButtonClass(): string
    {
        return $this->shouldAddNextMonthButton() ? "" : "hide-me";
    }

    public function getCurrentMonthButtonClass(): string
    {
        return $this->shouldShowCurrentMonthButton() ? "" : "hide-me";
    }

    public function getPreviousMonthFilter(): string
    {
        $month = (($this->getMonth()) - 1) % 12;
        $year = ($this->getYear()) - ($month < 1 ? 1 : 0);
        return "filterMonth={$month}&filterYear={$year}";
    }

    public function getNextMonthFilter(): string
    {
        $month = (($this->getMonth()) + 1) % 12;
        $year = ($this->getYear()) + ($month > 12 ? 1 : 0);
        return "filterMonth={$month}&filterYear={$year}";
    }

    public function getRemarksForCurrentMonth(): string
    {
        return $this->remarks->remarks;
    }

    public function getRemarksMonth(): int
    {
        return $this->remarks->month ?? $this->getMonth();
    }

    public function getRemarksYear(): int
    {
        return $this->remarks->year ?? $this->getYear();
    }

    public function getRemarksId(): string
    {
        return $this->remarks->id;
    }

    public function isEditingRemarks(): bool
    {
        return empty($this->remarks->id) === false;
    }

    public function getRemarksButtonText(): string
    {
        return sprintf("%s Remarks", $this->isEditingRemarks() ? "View" : "Add");
    }

    public function hasFilter(): bool
    {
        return $this->filters->hasFilter();
    }

    public function hasRemarks(): bool
    {
        return $this->remarks->remarks !== "";
    }

    public function getMonthFilter(): int
    {
        return $this->filters->filterMonth;
    }

    public function getYearFilter(): int
    {
        return $this->filters->filterYear;
    }

    public function getMonthQuery(): string
    {
        return "filterMonth={$this->getMonth()}&filterYear={$this->getYear()}";
    }

    public function getEntryDeleteUrl(TimeEntry $entry): string
    {
        return sprintf(
            "%s/%s",
            $this->deleteUrl,
            $this->hasFilter()
                ? sprintf("%s?%s", $entry->getPrimaryKey(), $this->getMonthQuery())
                : (string) $entry->getPrimaryKey()
        );
    }

    public function getMonthReportEmailUrl(): string
    {
        return sprintf(
            "%s%s",
            $this->emailUrl,
            $this->hasFilter() ? sprintf("?%s", $this->getMonthQuery()) : "",
        );
    }

    public function listTitle(): string
    {

        return $this->isCurrentMonth() ? "Recent Activities" : $this->getMonthTitle();
    }

    public function getMonthTitle(): string
    {
        $month = $this->getMonth();
        $year = $this->getYear();
        $month = DateTimeImmutable::createFromFormat("!m", (string)$month);
        return sprintf("%s %d", $month ? $month->format("F") : "", $year);
    }

    private function isCurrentMonth(): bool
    {
        $month = $this->getMonth();
        $year = $this->getYear();
        return $month === TimeEntry::getDefaultValue("month")
            && $year === TimeEntry::getDefaultValue("year")
            || $year === 0
            || $month === 0;
    }

    public function getMonth(): int
    {
        return $this->filters->getMonth();
    }

    public function getYear(): int
    {
        return $this->filters->getYear();
    }

    private function shouldAddNextMonthButton(): bool
    {
        $date = new DateTimeImmutable("now");
        $currentMonth = (int)$date->format("m");
        $currentYear = (int)$date->format("Y");
        $filterMonth = $this->getMonth();
        $filterYear = $this->getYear();
        return ($filterMonth >= $currentMonth && $filterYear >= $currentYear) === false;
    }

    private function shouldShowCurrentMonthButton(): bool
    {
        $date = new DateTimeImmutable("now");
        $currentMonth = (int)$date->format("m");
        $currentYear = (int)$date->format("Y");
        $filterMonth = $this->getMonth();
        $filterYear = $this->getYear();
        return ($filterMonth < $currentMonth - 1 && $filterYear <= $currentYear);
    }
}
