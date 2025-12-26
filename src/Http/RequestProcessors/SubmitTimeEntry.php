<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use Phpolar\{
    Model\Model,
    Storage\NotFound
};
use EricFortmeyer\ActivityLog\Services\{
    TimeEntryService,
    RemarksForMonthService,
    TemplateBinder
};
use EricFortmeyer\ActivityLog\{
    MonthFilters,
    TimeEntry,
    RemarksForMonth
};
use EricFortmeyer\ActivityLog\UserInterface\Contexts\TimeEntriesContext;
use EricFortmeyer\ActivityLog\Utils\Hasher;
use Phpolar\Phpolar\Auth\Authorize;

final class SubmitTimeEntry extends AbstractTenantBasedRequestProcessor
{
    public function __construct(
        private readonly string $appVersion,
        private readonly TimeEntryService $timeEntryService,
        private readonly RemarksForMonthService $remarksForMonthService,
        private readonly TemplateBinder $templateEngine,
        readonly Hasher $hasher,
    ) {
        parent::__construct(hasher: $hasher);
    }

    #[Authorize]
    public function process(
        #[Model] TimeEntry $entry = new TimeEntry(),
        #[Model] MonthFilters $monthFilters = new MonthFilters()
    ): string {
        if ($entry->isValid()) {
            $this->timeEntryService->save($entry);
        }
        $entry->isPosted();

        $month = $monthFilters->getMonth();
        $year = $monthFilters->getYear();
        $timeEntries = $this->timeEntryService->getAllByMonth(
            month: $month,
            year: $year,
            tenantId: $this->getTenantId(),
        );
        $remarks = $this->remarksForMonthService->get(RemarksForMonth::getIdFromMonth(
            year: $year,
            month: $month,
            tenantId: $this->getTenantId(),
        ));

        return $this->templateEngine->apply(
            "index",
            $remarks instanceof NotFound
                ? new TimeEntriesContext(
                    appVersion: $this->appVersion,
                    timeEntries: $timeEntries,
                    tenantId: $this->getTenantId(),
                    currentEntry: $entry,
                    filters: $monthFilters,
                    user: $this->user
                )
                : new TimeEntriesContext(
                    appVersion: $this->appVersion,
                    timeEntries: $timeEntries,
                    tenantId: $this->getTenantId(),
                    currentEntry: $entry,
                    filters: $monthFilters,
                    remarks: $remarks,
                    user: $this->user
                )
        );
    }
}
