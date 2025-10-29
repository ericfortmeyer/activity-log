<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\HttpRequestProcessor\RequestProcessorInterface;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\PurePhp\HtmlSafeContext;
use EricFortmeyer\ActivityLog\{NotFoundContext, TimeEntriesContext};
use Phpolar\Model\Model;
use Phpolar\Storage\NotFound;

/**
 * Class DeleteTimeEntry
 *
 * @package EricFortmeyer\ActivityLog
 */
final class DeleteTimeEntry implements RequestProcessorInterface
{
    public function __construct(
        private readonly TimeEntryService $timeEntryService,
        private readonly RemarksForMonthService $remarksForMonthService,
        private readonly TemplateEngine $templateEngine,
    ) {}

    public function process(
        #[Model] MonthFilters $monthFilters = new MonthFilters(),
        string $id = "",
    ): string {
        $deletedEntry = $this->timeEntryService->delete($id);

        if ($deletedEntry instanceof NotFound) {
            return (string) $this->templateEngine->apply(
                "404",
                new HtmlSafeContext(new NotFoundContext("The time entry to delete was not found."))
            );
        }

        $month = $monthFilters->getMonth();
        $year = $monthFilters->getYear();

        $timeEntries = $this->timeEntryService->getAllByMonth(month: $month, year: $year);
        $remarks = $this->remarksForMonthService->get(RemarksForMonth::getIdFromMonth(year: $year, month: $month));

        return (string) $this->templateEngine->apply(
            "index",
            new HtmlSafeContext(
                $remarks instanceof NotFound
                    ? new TimeEntriesContext($timeEntries, $deletedEntry, $monthFilters)
                    : new TimeEntriesContext($timeEntries, $deletedEntry, $monthFilters, $remarks)
            )
        );
    }
}
