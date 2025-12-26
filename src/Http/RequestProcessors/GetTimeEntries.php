<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use Phpolar\Phpolar\Auth\{
    Authorize
};
use Phpolar\{
    Model\Model,
    Storage\NotFound
};
use EricFortmeyer\ActivityLog\{RemarksForMonth, MonthFilters, CreditHours, Tenant, TimeEntry};
use EricFortmeyer\ActivityLog\Services\{
    RemarksForMonthService,
    CreditHoursService,
    TemplateBinder,
    TenantService,
    TimeEntryService
};
use EricFortmeyer\ActivityLog\UserInterface\Contexts\{TimeEntriesContext, BadRequestContext};
use EricFortmeyer\ActivityLog\Utils\Hasher;

/**
 * Class GetTimeEntries
 *
 * @package EricFortmeyer\ActivityLog
 */
final class GetTimeEntries extends AbstractTenantBasedRequestProcessor
{
    public function __construct(
        private readonly string $appVersion,
        private readonly TenantService $tenantService,
        private readonly TimeEntryService $timeEntryService,
        private readonly RemarksForMonthService $remarksForMonthService,
        private readonly CreditHoursService $creditHoursService,
        private readonly TemplateBinder $templateEngine,
        readonly Hasher $hasher,
    ) {
        parent::__construct($hasher);
    }

    /**
     * Process the request to get all time entries.
     *
     * @return string The rendered template
     */
    #[Authorize]
    public function process(
        #[Model] TimeEntry $timeEntry = new TimeEntry(),
        #[Model] MonthFilters $monthFilters = new MonthFilters(),
    ): string {
        if ($monthFilters->isValid() === false) {
            return $this->templateEngine->apply(
                "400",
                new BadRequestContext(message: "Something is wrong with the request.")
            );
        }

        $this->initTenantIfNotExists();

        $timeEntry->tenantId ??= $this->getTenantId();
        $month = $monthFilters->getMonth();
        $year = $monthFilters->getYear();
        TimeEntry::setUninitializedValues(timeEntry: $timeEntry, month: $month, year: $year);
        $timeEntries = $this->timeEntryService->getAllByMonth(
            month: $month,
            year: $year,
            tenantId: $this->getTenantId(),
        );
        $remarks = $this->remarksForMonthService->get(RemarksForMonth::getIdFromMonth(
            month: $month,
            year: $year,
            tenantId: $this->getTenantId(),
        ));
        $creditHours = $this->creditHoursService->get(CreditHours::getIdFromMonth(
            month: $month,
            year: $year,
            tenantId: $this->getTenantId(),
        ));
        return $this->templateEngine->apply(
            "index",
            match (true) {
                $remarks instanceof NotFound && $creditHours instanceof NotFound =>
                new TimeEntriesContext(
                    appVersion: $this->appVersion,
                    timeEntries: $timeEntries,
                    tenantId: $this->getTenantId(),
                    currentEntry: $timeEntry,
                    filters: $monthFilters,
                    user: $this->user
                ),
                $creditHours instanceof CreditHours && $remarks instanceof RemarksForMonth =>
                new TimeEntriesContext(
                    appVersion: $this->appVersion,
                    timeEntries: $timeEntries,
                    tenantId: $this->getTenantId(),
                    currentEntry: $timeEntry,
                    filters: $monthFilters,
                    remarks: $remarks,
                    creditHours: $creditHours,
                    user: $this->user
                ),
                $creditHours instanceof CreditHours && $remarks instanceof NotFound =>
                new TimeEntriesContext(
                    appVersion: $this->appVersion,
                    timeEntries: $timeEntries,
                    tenantId: $this->getTenantId(),
                    currentEntry: $timeEntry,
                    filters: $monthFilters,
                    creditHours: $creditHours,
                    user: $this->user,
                ),
                $remarks instanceof RemarksForMonth && $creditHours instanceof NotFound =>
                new TimeEntriesContext(
                    appVersion: $this->appVersion,
                    timeEntries: $timeEntries,
                    tenantId: $this->getTenantId(),
                    currentEntry: $timeEntry,
                    filters: $monthFilters,
                    remarks: $remarks,
                    user: $this->user
                ),
                default =>
                new TimeEntriesContext(
                    appVersion: $this->appVersion,
                    user: $this->user,
                    tenantId: $this->getTenantId()
                )
            }
        );
    }

    private function initTenantIfNotExists(): void
    {
        $tenantId = $this->getTenantId();

        if ($this->tenantService->exists($tenantId) === true) {
            return;
        }

        $this->tenantService->save(
            new Tenant(["id" => $tenantId])
        );
    }
}
