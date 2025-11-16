<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use Phpolar\{
    Model\Model,
    PurePhp\TemplateEngine,
    PurePhp\HtmlSafeContext,
    Storage\NotFound
};
use EricFortmeyer\ActivityLog\Services\{TimeEntryService, RemarksForMonthService};
use EricFortmeyer\ActivityLog\{MonthFilters, TimeEntry, RemarksForMonth};
use EricFortmeyer\ActivityLog\UserInterface\Contexts\TimeEntriesContext;
use Phpolar\Phpolar\Auth\AbstractProtectedRoutable;
use Phpolar\Phpolar\Auth\Authorize;

final class SubmitTimeEntry extends AbstractProtectedRoutable
{
    public function __construct(
        private readonly TimeEntryService $timeEntryService,
        private readonly RemarksForMonthService $remarksForMonthService,
        private readonly TemplateEngine $templateEngine,
    ) {}

    #[Authorize]
    public function process(
        #[Model] TimeEntry $entry = new TimeEntry(),
        #[Model] MonthFilters $monthFilters = new MonthFilters()
    ): string {
        if ($entry->isValid() === true) {
            $entry->create($this->user);
            $this->timeEntryService->save($entry);
        }
        $entry->isPosted();

        $month = $monthFilters->getMonth();
        $year = $monthFilters->getYear();
        $timeEntries = $this->timeEntryService->getAllByMonth(
            month: $month,
            year: $year,
            tenantId: $this->user->nickname
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
                        currentEntry: $entry,
                        filters: $monthFilters,
                        user: $this->user
                    )
                    : new TimeEntriesContext(
                        timeEntries: $timeEntries,
                        currentEntry: $entry,
                        filters: $monthFilters,
                        remarks: $remarks,
                        user: $this->user
                    )
            ),
        );
    }
}
