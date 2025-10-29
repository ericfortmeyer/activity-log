<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\HttpRequestProcessor\RequestProcessorInterface;
use Phpolar\Model\Model;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\PurePhp\HtmlSafeContext;
use Phpolar\Storage\NotFound;

final class SubmitTimeEntry implements RequestProcessorInterface
{
    public function __construct(
        private readonly TimeEntryService $timeEntryService,
        private readonly RemarksForMonthService $remarksForMonthService,
        private readonly TemplateEngine $templateEngine,
    ) {}

    public function process(
        #[Model] TimeEntry $entry = new TimeEntry(),
        #[Model] MonthFilters $monthFilters = new MonthFilters()
    ): string {
        if ($entry->isValid() === true) {
            $entry->create();
            $this->timeEntryService->save($entry);
        }
        $entry->isPosted();

        $month = $monthFilters->getMonth();
        $year = $monthFilters->getYear();
        $timeEntries = $this->timeEntryService->getAllByMonth(month: $month, year: $year);
        $remarks = $this->remarksForMonthService->get(RemarksForMonth::getIdFromMonth(year: $year, month: $month));

        return (string) $this->templateEngine->apply(
            "index",
            new HtmlSafeContext(
                $remarks instanceof NotFound
                    ? new TimeEntriesContext($timeEntries, $entry, $monthFilters)
                    : new TimeEntriesContext($timeEntries, $entry, $monthFilters, $remarks)
            ),
        );
    }
}
