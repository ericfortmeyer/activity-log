<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use Phpolar\Model\Model;
use Phpolar\Phpolar\Auth\{
    Authorize
};
use Phpolar\Storage\NotFound;
use EricFortmeyer\ActivityLog\UserInterface\Contexts\{NotFoundContext, TimeEntriesContext};
use EricFortmeyer\ActivityLog\Services\{TimeEntryService, RemarksForMonthService, TemplateBinder};
use EricFortmeyer\ActivityLog\{MonthFilters, RemarksForMonth, TimeEntry};
use EricFortmeyer\ActivityLog\Utils\Hasher;

/**
 * Class DeleteTimeEntry
 *
 * @package EricFortmeyer\ActivityLog
 */
final class DeleteTimeEntry extends AbstractTenantBasedRequestProcessor
{
    public function __construct(
        private readonly string $appVersion,
        private readonly TimeEntryService $timeEntryService,
        private readonly RemarksForMonthService $remarksForMonthService,
        private readonly TemplateBinder $templateEngine,
        readonly Hasher $hasher,
    ) {
        parent::__construct($hasher);
    }

    #[Authorize]
    public function process(
        #[Model] MonthFilters $monthFilters = new MonthFilters(),
        string $id = "",
    ): string {
        $deletedEntry = $this->timeEntryService->delete($id);

        if ($deletedEntry instanceof NotFound) {
            return $this->templateEngine->apply(
                "404",
                new NotFoundContext(message: "The time entry to delete was not found.")
            );
        }

        $newEntryForForm = new TimeEntry();


        $month = $monthFilters->getMonth();
        $year = $monthFilters->getYear();
        TimeEntry::setUninitializedValues(timeEntry: $newEntryForForm, month: $month, year: $year);

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

        return  $this->templateEngine->apply(
            "index",
            $remarks instanceof NotFound
                ? new TimeEntriesContext(
                    appVersion: $this->appVersion,
                    timeEntries: $timeEntries,
                    tenantId: $this->getTenantId(),
                    currentEntry: $newEntryForForm,
                    filters: $monthFilters,
                    user: $this->user
                )
                : new TimeEntriesContext(
                    appVersion: $this->appVersion,
                    timeEntries: $timeEntries,
                    tenantId: $this->getTenantId(),
                    currentEntry: $newEntryForForm,
                    filters: $monthFilters,
                    remarks: $remarks,
                    user: $this->user
                )
        );
    }
}
