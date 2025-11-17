<?php

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use Phpolar\{
    Model\Model,
    PurePhp\HtmlSafeContext,
    PurePhp\TemplateEngine,
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
    TimeEntryService
};
use EricFortmeyer\ActivityLog\UserInterface\Contexts\TimeEntriesContext;

final class SaveRemarksForMonth extends AbstractTenantBasedRequestProcessor
{
    public function __construct(
        private readonly RemarksForMonthService $remarksService,
        private readonly CreditHoursService $creditHoursService,
        private readonly TimeEntryService $timeEntryService,
        private readonly TemplateEngine $templateEngine,
        readonly string $hashingKey = "",
    ) {
        parent::__construct(hashingKey: $hashingKey);
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
            month: $remarks->month ?? TimeEntry::getDefaultValue("month"),
            year: $remarks->year ?? TimeEntry::getDefaultValue("year"),
            tenantId: $this->getTenantId(),
        );
        $currentEntry = new TimeEntry();
        return (string) $this->templateEngine->apply(
            "index",
            new HtmlSafeContext(
                $creditHours instanceof NotFound
                    ? new TimeEntriesContext(
                        timeEntries: $timeEntries,
                        currentEntry: $currentEntry,
                        filters: $monthFilters,
                        remarks: $remarks,
                        user: $this->user,
                    )
                    : new TimeEntriesContext(
                        timeEntries: $timeEntries,
                        currentEntry: $currentEntry,
                        filters: $monthFilters,
                        remarks: $remarks,
                        creditHours: $creditHours,
                        user: $this->user,
                    )
            )
        );
    }
}
