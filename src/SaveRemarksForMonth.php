<?php

namespace EricFortmeyer\ActivityLog;

use Phpolar\HttpRequestProcessor\RequestProcessorInterface;
use Phpolar\Model\Model;
use Phpolar\PurePhp\HtmlSafeContext;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\Storage\NotFound;

final class SaveRemarksForMonth implements RequestProcessorInterface
{
    public function __construct(
        private readonly RemarksForMonthService $remarksService,
        private readonly CreditHoursService $creditHoursService,
        private readonly TimeEntryService $timeEntryService,
        private readonly TemplateEngine $templateEngine,
    ) {
    }

    public function process(
        #[Model] RemarksForMonth $remarks = new RemarksForMonth(),
        #[Model] MonthFilters $monthFilters = new MonthFilters()
    ): string {
        if ($remarks->isValid() === true) {
            $this->remarksService->save($remarks);
        }
        $remarks->isPosted();

        $creditHours = $this->creditHoursService->get(
            CreditHours::getIdFromMonth(
                year: $remarks->year,
                month: $remarks->month,
            ),
        );

        $timeEntries = $this->timeEntryService->getAllByMonth(
            $remarks->month ?? TimeEntry::getDefaultValue("month"),
            $remarks->year ?? TimeEntry::getDefaultValue("year")
        );
        $currentEntry = new TimeEntry();
        return (string) $this->templateEngine->apply(
            "index",
            new HtmlSafeContext(
                $creditHours instanceof NotFound
                    ? new TimeEntriesContext(
                        timeEntries: $timeEntries,
                        currentEntry: $currentEntry,
                        filters: $monthFilters,
                        remarks: $remarks
                    )
                    : new TimeEntriesContext(
                        timeEntries: $timeEntries,
                        currentEntry: $currentEntry,
                        filters: $monthFilters,
                        remarks: $remarks,
                        creditHours: $creditHours,
                    )
            )
        );
    }
}
