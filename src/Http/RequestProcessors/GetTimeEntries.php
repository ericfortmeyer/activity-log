<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use Phpolar\Phpolar\Auth\{
    AbstractProtectedRoutable,
    Authorize
};
use Phpolar\{
    Model\Model,
    PurePhp\TemplateEngine,
    PurePhp\HtmlSafeContext,
    Storage\NotFound
};
use EricFortmeyer\ActivityLog\{RemarksForMonth, MonthFilters, CreditHours, TimeEntry};
use EricFortmeyer\ActivityLog\Services\{RemarksForMonthService, CreditHoursService, TimeEntryService};
use EricFortmeyer\ActivityLog\UserInterface\Contexts\{TimeEntriesContext, BadRequestContext};

/**
 * Class GetTimeEntries
 *
 * @package EricFortmeyer\ActivityLog
 */
final class GetTimeEntries extends AbstractProtectedRoutable
{
    public function __construct(
        private readonly TimeEntryService $timeEntryService,
        private readonly RemarksForMonthService $remarksForMonthService,
        private readonly CreditHoursService $creditHoursService,
        private readonly TemplateEngine $templateEngine,
    ) {}

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
        if (
            $monthFilters->isValid() === false
            || (isset($timeEntry->id) && $timeEntry->isValid() === false)
        ) {
            return (string) $this->templateEngine->apply(
                "400",
                new HtmlSafeContext(
                    new BadRequestContext(message: "Something is wrong with the request.")
                )
            );
        }
        $timeEntry->tenantId ??= $this->user->nickname;
        $month = $monthFilters->getMonth();
        $year = $monthFilters->getYear();
        $timeEntries = $this->timeEntryService->getAllByMonth(
            month: $month,
            year: $year,
            tenantId: $this->user->nickname,
        );
        $remarks = $this->remarksForMonthService->get(RemarksForMonth::getIdFromMonth(
            month: $month,
            year: $year,
            tenantId: $this->user->nickname
        ));
        $creditHours = $this->creditHoursService->get(CreditHours::getIdFromMonth(
            month: $month,
            year: $year,
            tenantId: $this->user->nickname
        ));
        return (string) $this->templateEngine->apply(
            "index",
            new HtmlSafeContext(
                $this->getContext(
                    timeEntries: $timeEntries,
                    timeEntry: $timeEntry,
                    monthFilters: $monthFilters,
                    remarks: $remarks,
                    creditHours: $creditHours,
                )
            ),
        );
    }

    /**
     * @param TimeEntry[] $timeEntries
     */
    private function getContext(
        array $timeEntries,
        TimeEntry $timeEntry,
        MonthFilters $monthFilters,
        RemarksForMonth| NotFound $remarks,
        CreditHours|NotFound $creditHours,
    ): TimeEntriesContext {
        return match (true) {
            $remarks instanceof NotFound && $creditHours instanceof NotFound =>
            new TimeEntriesContext(
                timeEntries: $timeEntries,
                currentEntry: $timeEntry,
                filters: $monthFilters,
                user: $this->user
            ),
            $creditHours instanceof CreditHours && $remarks instanceof RemarksForMonth =>
            new TimeEntriesContext(
                timeEntries: $timeEntries,
                currentEntry: $timeEntry,
                filters: $monthFilters,
                remarks: $remarks,
                creditHours: $creditHours,
                user: $this->user
            ),
            $creditHours instanceof CreditHours && $remarks instanceof NotFound =>
            new TimeEntriesContext(
                timeEntries: $timeEntries,
                currentEntry: $timeEntry,
                filters: $monthFilters,
                creditHours: $creditHours,
                user: $this->user,
            ),
            $remarks instanceof RemarksForMonth && $creditHours instanceof NotFound =>
            new TimeEntriesContext(
                timeEntries: $timeEntries,
                currentEntry: $timeEntry,
                filters: $monthFilters,
                remarks: $remarks,
                user: $this->user
            ),
            default =>
            new TimeEntriesContext(user: $this->user)
        };
    }
}
