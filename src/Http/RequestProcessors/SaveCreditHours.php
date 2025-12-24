<?php

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use Phpolar\Phpolar\Auth\Authorize;
use Phpolar\{
    Model\Model,
    Storage\NotFound
};
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

final class SaveCreditHours extends AbstractTenantBasedRequestProcessor
{
    public function __construct(
        private readonly CreditHoursService $creditHoursService,
        private readonly RemarksForMonthService $remarksService,
        private readonly TimeEntryService $timeEntryService,
        private readonly TemplateBinder $templateEngine,
        readonly Hasher $hasher,
    ) {
        parent::__construct($hasher);
    }

    #[Authorize]
    public function process(
        #[Model] CreditHours $creditHours = new CreditHours(),
        #[Model] MonthFilters $monthFilters = new MonthFilters()
    ): string {
        if ($creditHours->isValid() === true) {
            $this->creditHoursService->save($creditHours, $this->getTenantId());
        }
        $creditHours->isPosted();

        $remarks = $this->remarksService->get(
            RemarksForMonth::getIdFromMonth(
                year: $creditHours->year,
                month: $creditHours->month,
                tenantId: $this->getTenantId(),
            ),
        );

        $timeEntries = $this->timeEntryService->getAllByMonth(
            month: $creditHours->month,
            year: $creditHours->year,
            tenantId: $this->getTenantId(),
        );
        $currentEntry = new TimeEntry();
        TimeEntry::setUninitializedValues(
            $currentEntry,
            $creditHours->month,
            $creditHours->year,
        );
        return $this->templateEngine->apply(
            "index",
            $remarks instanceof NotFound
                ? new TimeEntriesContext(
                    timeEntries: $timeEntries,
                    tenantId: $this->getTenantId(),
                    currentEntry: $currentEntry,
                    filters: $monthFilters,
                    creditHours: $creditHours,
                    user: $this->user
                )
                : new TimeEntriesContext(
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
