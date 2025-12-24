<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use EricFortmeyer\ActivityLog\{
    CreditHours,
    TimeEntry,
    EmailConfig,
    MonthFilters,
    RemarksForMonth,
    EmailReport
};
use EricFortmeyer\ActivityLog\UserInterface\Contexts\TimeEntriesContext;
use EricFortmeyer\ActivityLog\Services\{
    TimeEntryService,
    RemarksForMonthService,
    CreditHoursService,
    TemplateBinder,
};
use EricFortmeyer\ActivityLog\UserInterface\Contexts\EmailReportContext;
use EricFortmeyer\ActivityLog\UserInterface\Contexts\ServerErrorContext;
use EricFortmeyer\ActivityLog\Utils\Hasher;
use Phpolar\Model\Model;
use Phpolar\Phpolar\Auth\Authorize;
use Phpolar\PurePhp\HtmlSafeContext;
use Phpolar\Storage\NotFound;

final class EmailReportForMonth extends AbstractTenantBasedRequestProcessor
{
    public function __construct(
        private readonly EmailConfig $mailConfig,
        private readonly TimeEntryService $timeEntryService,
        private readonly RemarksForMonthService $remarksService,
        private readonly CreditHoursService $creditHoursService,
        private readonly TemplateBinder $templateEngine,
        readonly Hasher $hasher,
    ) {
        parent::__construct($hasher);
    }

    #[Authorize]
    public function process(
        #[Model] EmailReport $emailReport = new EmailReport(),
        #[Model] MonthFilters $monthFilters = new MonthFilters(),
    ): string {
        $month = $emailReport->month;
        $year = $emailReport->year;
        $timeEntries = $this->timeEntryService->getAllByMonth(
            month: $month,
            year: $year,
            tenantId: $this->getTenantId(),
        );
        $remarks = $this->remarksService->get(RemarksForMonth::getIdFromMonth(
            month: $month,
            year: $year,
            tenantId: $this->getTenantId(),
        ));
        $creditHours = $this->creditHoursService->get(CreditHours::getIdFromMonth(
            month: $month,
            year: $year,
            tenantId: $this->getTenantId(),
        ));
        $currentEntry = new TimeEntry();
        TimeEntry::setUninitializedValues(
            $currentEntry,
            $month,
            $year,
        );

        if ($emailReport->isValid() === true) {
            $success = mail(
                to: $emailReport->mailTo,
                subject: $emailReport->getSubject($this->user),
                message: $this->templateEngine->apply(
                    "email-report",
                    new EmailReportContext(
                        emailReport: $emailReport,
                        timeEntries: $timeEntries,
                        currentEntry: $currentEntry,
                        filters: $monthFilters,
                        remarks: $remarks,
                        creditHours: $creditHours,
                    ),
                ),
                additional_headers: $this->mailConfig->headers,
            );

            if ($success === false) {
                $error = error_get_last();
                if ($error !== null) {
                    ["message" => $message] = $error;
                    return $this->templateEngine->apply(
                        "500",
                        new HtmlSafeContext(
                            new ServerErrorContext(
                                message: $message,
                            ),
                        ),
                    );
                }
            }

            $emailReport->isPosted();
        }


        return $this->templateEngine->apply(
            "index",
            $this->getContext(
                currentEntry: $currentEntry,
                timeEntries: $timeEntries,
                monthFilters: $monthFilters,
                remarks: $remarks,
                creditHours: $creditHours,
            )
        );
    }

    /**
     * @param TimeEntry[] $timeEntries
     */
    private function getContext(
        TimeEntry $currentEntry,
        array $timeEntries,
        MonthFilters $monthFilters,
        RemarksForMonth| NotFound $remarks,
        CreditHours|NotFound $creditHours,
    ): TimeEntriesContext {
        return match (true) {
            $remarks instanceof NotFound && $creditHours instanceof NotFound =>
            new TimeEntriesContext(
                currentEntry: $currentEntry,
                timeEntries: $timeEntries,
                tenantId: $this->getTenantId(),
                filters: $monthFilters,
                user: $this->user
            ),
            $creditHours instanceof CreditHours && $remarks instanceof NotFound =>
            new TimeEntriesContext(
                currentEntry: $currentEntry,
                timeEntries: $timeEntries,
                tenantId: $this->getTenantId(),
                filters: $monthFilters,
                creditHours: $creditHours,
                user: $this->user
            ),
            $creditHours instanceof CreditHours && $remarks instanceof RemarksForMonth =>
            new TimeEntriesContext(
                currentEntry: $currentEntry,
                timeEntries: $timeEntries,
                tenantId: $this->getTenantId(),
                filters: $monthFilters,
                remarks: $remarks,
                creditHours: $creditHours,
                user: $this->user,
            ),
            $remarks instanceof RemarksForMonth && $creditHours instanceof NotFound =>
            new TimeEntriesContext(
                currentEntry: $currentEntry,
                timeEntries: $timeEntries,
                tenantId: $this->getTenantId(),
                filters: $monthFilters,
                remarks: $remarks,
                user: $this->user
            ),
            default =>
            new TimeEntriesContext(
                currentEntry: $currentEntry,
                user: $this->user,
                tenantId: $this->getTenantId(),
            )
        };
    }
}
