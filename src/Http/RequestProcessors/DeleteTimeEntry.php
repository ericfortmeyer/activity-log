<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use Phpolar\PurePhp\{
    TemplateEngine,
    HtmlSafeContext
};
use Phpolar\Model\Model;
use Phpolar\Phpolar\Auth\{
    AbstractProtectedRoutable,
    Authorize
};
use Phpolar\Storage\NotFound;
use EricFortmeyer\ActivityLog\UserInterface\Contexts\{NotFoundContext, TimeEntriesContext};
use EricFortmeyer\ActivityLog\Services\{TimeEntryService, RemarksForMonthService};
use EricFortmeyer\ActivityLog\{MonthFilters, RemarksForMonth};

/**
 * Class DeleteTimeEntry
 *
 * @package EricFortmeyer\ActivityLog
 */
final class DeleteTimeEntry extends AbstractProtectedRoutable
{
    public function __construct(
        private readonly TimeEntryService $timeEntryService,
        private readonly RemarksForMonthService $remarksForMonthService,
        private readonly TemplateEngine $templateEngine,
    ) {}

    #[Authorize]
    public function process(
        #[Model] MonthFilters $monthFilters = new MonthFilters(),
        string $id = "",
    ): string {
        $deletedEntry = $this->timeEntryService->delete($id);

        if ($deletedEntry instanceof NotFound) {
            return (string) $this->templateEngine->apply(
                "404",
                new HtmlSafeContext(new NotFoundContext(message: "The time entry to delete was not found."))
            );
        }

        $month = $monthFilters->getMonth();
        $year = $monthFilters->getYear();

        $timeEntries = $this->timeEntryService->getAllByMonth(
            month: $month,
            year: $year,
            tenantId: $this->user->nickname,
        );
        $remarks = $this->remarksForMonthService->get(RemarksForMonth::getIdFromMonth(
            year: $year,
            month: $month,
            tenantId: $this->user->nickname,
        ));

        return (string) $this->templateEngine->apply(
            "index",
            new HtmlSafeContext(
                $remarks instanceof NotFound
                    ? new TimeEntriesContext(
                        timeEntries: $timeEntries,
                        currentEntry: $deletedEntry,
                        filters: $monthFilters,
                        user: $this->user
                    )
                    : new TimeEntriesContext(
                        timeEntries: $timeEntries,
                        currentEntry: $deletedEntry,
                        filters: $monthFilters,
                        remarks: $remarks,
                        user: $this->user
                    )
            )
        );
    }
}
