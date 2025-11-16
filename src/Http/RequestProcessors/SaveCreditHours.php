<?php

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use Phpolar\Phpolar\Auth\{
    AbstractProtectedRoutable,
    Authorize
};
use Phpolar\{
    Model\Model,
    PurePhp\HtmlSafeContext,
    PurePhp\TemplateEngine,
    Storage\NotFound
};
use EricFortmeyer\ActivityLog\{RemarksForMonth, MonthFilters, CreditHours, TimeEntry};
use EricFortmeyer\ActivityLog\Services\{RemarksForMonthService, CreditHoursService, TimeEntryService};
use EricFortmeyer\ActivityLog\UserInterface\Contexts\TimeEntriesContext;

final class SaveCreditHours extends AbstractProtectedRoutable
{
    public function __construct(
        private readonly CreditHoursService $creditHoursService,
        private readonly RemarksForMonthService $remarksService,
        private readonly TimeEntryService $timeEntryService,
        private readonly TemplateEngine $templateEngine,
    ) {}

    #[Authorize]
    public function process(
        #[Model] CreditHours $creditHours = new CreditHours(),
        #[Model] MonthFilters $monthFilters = new MonthFilters()
    ): string {
        if ($creditHours->isValid() === true) {
            $this->creditHoursService->save($creditHours, $this->user);
        }
        $creditHours->isPosted();

        $remarks = $this->remarksService->get(
            RemarksForMonth::getIdFromMonth(
                year: $creditHours->year,
                month: $creditHours->month,
                tenantId: $this->user->nickname,
            ),
        );

        $timeEntries = $this->timeEntryService->getAllByMonth(
            month: $creditHours->month ?? TimeEntry::getDefaultValue("month"),
            year: $creditHours->year ?? TimeEntry::getDefaultValue("year"),
            tenantId: $this->user->nickname,
        );
        $currentEntry = new TimeEntry();
        return (string) $this->templateEngine->apply(
            "index",
            new HtmlSafeContext(
                $remarks instanceof NotFound
                    ? new TimeEntriesContext(
                        timeEntries: $timeEntries,
                        currentEntry: $currentEntry,
                        filters: $monthFilters,
                        creditHours: $creditHours,
                        user: $this->user
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
