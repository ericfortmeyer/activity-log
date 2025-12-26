<?php

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use Phpolar\{
    Model\Model,
    Storage\NotFound
};
use Phpolar\Phpolar\Auth\Authorize;
use EricFortmeyer\ActivityLog\{
    RemarksForMonth,
    MonthFilters,
    CreditHours,
    TimeEntry
};
use EricFortmeyer\ActivityLog\Services\{
    RemarksForMonthService,
    CreditHoursService,
    TemplateBinder,
    TimeEntryService
};
use EricFortmeyer\ActivityLog\UserInterface\Contexts\TimeEntriesContext;
use EricFortmeyer\ActivityLog\Utils\Hasher;

final class SaveRemarksForMonth extends AbstractTenantBasedRequestProcessor
{
    public function __construct(
        private readonly string $appVersion,
        private readonly RemarksForMonthService $remarksService,
        private readonly CreditHoursService $creditHoursService,
        private readonly TimeEntryService $timeEntryService,
        private readonly TemplateBinder $templateEngine,
        readonly Hasher $hasher,
    ) {
        parent::__construct($hasher);
    }

    #[Authorize]
    public function process(
        #[Model] RemarksForMonth $remarks = new RemarksForMonth(),
        #[Model] MonthFilters $monthFilters = new MonthFilters()
    ): string {
        if ($remarks->isValid() === true) {
            $this->remarksService->save($remarks, $this->getTenantId());
        }
        $remarks->isPosted();

        $creditHours = $this->creditHoursService->get(
            CreditHours::getIdFromMonth(
                year: $remarks->year,
                month: $remarks->month,
                tenantId: $this->getTenantId(),
            ),
        );

        $timeEntries = $this->timeEntryService->getAllByMonth(
            month: $remarks->month,
            year: $remarks->year,
            tenantId: $this->getTenantId(),
        );
        $currentEntry = new TimeEntry();
        TimeEntry::setUninitializedValues(
            $currentEntry,
            $remarks->month,
            $remarks->year,
        );
        return $this->templateEngine->apply(
            "index",
            $creditHours instanceof NotFound
                ? new TimeEntriesContext(
                    appVersion: $this->appVersion,
                    timeEntries: $timeEntries,
                    tenantId: $this->getTenantId(),
                    currentEntry: $currentEntry,
                    filters: $monthFilters,
                    remarks: $remarks,
                    user: $this->user,
                )
                : new TimeEntriesContext(
                    appVersion: $this->appVersion,
                    timeEntries: $timeEntries,
                    tenantId: $this->getTenantId(),
                    currentEntry: $currentEntry,
                    filters: $monthFilters,
                    remarks: $remarks,
                    creditHours: $creditHours,
                    user: $this->user,
                )
        );
    }
}
