<?php

namespace EricFortmeyer\ActivityLog;

use Phpolar\HttpRequestProcessor\RequestProcessorInterface;
use Phpolar\Model\Model;
use Phpolar\PurePhp\HtmlSafeContext;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\Storage\NotFound;

final class SaveCreditHours implements RequestProcessorInterface
{
    public function __construct(
        private readonly CreditHoursService $creditHoursService,
        private readonly RemarksForMonthService $remarksService,
        private readonly TimeEntryService $timeEntryService,
        private readonly TemplateEngine $templateEngine,
    ) {
    }

    public function process(
        #[Model] CreditHours $creditHours = new CreditHours(),
        #[Model] MonthFilters $monthFilters = new MonthFilters()
    ): string {
        if ($creditHours->isValid() === true) {
            $this->creditHoursService->save($creditHours);
        }
        $creditHours->isPosted();

        $remarks = $this->remarksService->get(
            RemarksForMonth::getIdFromMonth(
                year: $creditHours->year,
                month: $creditHours->month,
            ),
        );

        $timeEntries = $this->timeEntryService->getAllByMonth(
            $creditHours->month ?? TimeEntry::getDefaultValue("month"),
            $creditHours->year ?? TimeEntry::getDefaultValue("year")
        );
        $currentEntry = new TimeEntry();
        return (string) $this->templateEngine->apply(
            "index",
            new HtmlSafeContext(
                $remarks instanceof NotFound
                    ? new TimeEntriesContext(
                        timeEntries: $timeEntries,
                        currentEntry: $currentEntry,
                        filters: $monthFilters,
                        creditHours: $creditHours,
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
