<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\HttpRequestProcessor\RequestProcessorInterface;
use Phpolar\Model\Model;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\PurePhp\HtmlSafeContext;
use Phpolar\Storage\NotFound;

/**
 * Class GetTimeEntries
 *
 * @package EricFortmeyer\ActivityLog
 */
final class GetTimeEntries implements RequestProcessorInterface
{
    public function __construct(
        private readonly TimeEntryService $timeEntryService,
        private readonly RemarksForMonthService $remarksForMonthService,
        private readonly CreditHoursService $creditHoursService,
        private readonly TemplateEngine $templateEngine,
    ) {}

    /**
     * Process the request to get all time entries.
     *
     * @return string The rendered template
     */
    public function process(
        #[Model] TimeEntry $timeEntry = new TimeEntry(),
        #[Model] MonthFilters $monthFilters = new MonthFilters(),
    ): string {
        $month = $monthFilters->getMonth();
        $year = $monthFilters->getYear();
        $timeEntries = $this->timeEntryService->getAllByMonth(month: $month, year: $year);
        $remarks = $this->remarksForMonthService->get(RemarksForMonth::getIdFromMonth(month: $month, year: $year));
        $creditHours = $this->creditHoursService->get(CreditHours::getIdFromMonth(month: $month, year: $year));
        return (string) $this->templateEngine->apply(
            "index",
            new HtmlSafeContext(
                $this->getContext(
                    timeEntries: $timeEntries,
                    timeEntry: $timeEntry,
                    monthFilters: $monthFilters,
                    remarks: $remarks,
                    creditHours: $creditHours,
                )
            ),
        );
    }

    private function getContext(
        array $timeEntries,
        TimeEntry $timeEntry,
        MonthFilters $monthFilters,
        RemarksForMonth| NotFound $remarks,
        CreditHours|NotFound $creditHours,
    ): TimeEntriesContext {
        return match (true) {
            $remarks instanceof NotFound && $creditHours instanceof NotFound =>
            new TimeEntriesContext($timeEntries, $timeEntry, $monthFilters),
            $creditHours instanceof NotFound === false && $remarks instanceof NotFound === false =>
            new TimeEntriesContext($timeEntries, $timeEntry, $monthFilters, $remarks, $creditHours),
            $creditHours instanceof NotFound === false =>
            new TimeEntriesContext(
                timeEntries: $timeEntries,
                currentEntry: $timeEntry,
                filters: $monthFilters,
                creditHours: $creditHours
            ),
            $remarks instanceof NotFound === false =>
            new TimeEntriesContext($timeEntries, $timeEntry, $monthFilters, $remarks),
        };
    }
}
