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
};
use EricFortmeyer\ActivityLog\Utils\Hasher;
use Phpolar\Model\Model;
use Phpolar\PurePhp\HtmlSafeContext;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\Storage\NotFound;

final class EmailReportForMonth extends AbstractTenantBasedRequestProcessor
{
    public function __construct(
        private readonly EmailConfig $mailConfig,
        private readonly TimeEntryService $timeEntryService,
        private readonly RemarksForMonthService $remarksService,
        private readonly CreditHoursService $creditHoursService,
        private readonly TemplateEngine $templateEngine,
        readonly Hasher $hasher,
    ) {
        parent::__construct($hasher);
    }

    public function process(
        #[Model] EmailReport $emailReportContext = new EmailReport(),
        #[Model] MonthFilters $monthFilters = new MonthFilters(),
    ): string {
        $month = $monthFilters->getMonth();
        $year = $monthFilters->getYear();
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
        $context = $this->getContext(
            timeEntries: $timeEntries,
            monthFilters: $monthFilters,
            remarks: $remarks,
            creditHours: $creditHours,
        );
        $this->emailReport($emailReportContext, $context);
        return (string) $this->templateEngine->apply("index", new HtmlSafeContext($context));
    }

    private function emailReport(
        EmailReport $emailReportContext,
        TimeEntriesContext $timeEntriesContext,
    ): void {
        $success = mail(
            to: $emailReportContext->mailTo,
            subject: sprintf("Report for %s", $timeEntriesContext->getMonthTitle()),
            message: $this->getMessage(
                totalHours: $timeEntriesContext->getTotalHours(),
                creditHours: $timeEntriesContext->getCreditHours(),
                remarks: $timeEntriesContext->getRemarksForCurrentMonth(),
                shouldShowRemarks: $timeEntriesContext->hasRemarks(),
                shouldShowCreditHours: $timeEntriesContext->shouldShowCreditHours(),
            ),
            additional_headers: $this->mailConfig->headers,
        );

        if (!$success) {
            $error = error_get_last();
            if ($error !== null) {
                echo $error["message"];
            }
        }
    }

    private function getMessage(
        int $totalHours,
        int $creditHours,
        string $remarks,
        bool $shouldShowCreditHours,
        bool $shouldShowRemarks,
    ): string {
        return match (true) {
            $shouldShowCreditHours && $shouldShowRemarks =>
            <<<HTML
            <p>
                <strong>Total:</strong> {$totalHours} Hours
            </p>
            <p>
                <strong>Credit:</strong> {$creditHours} Hours
            </p>
            <p>
                <strong>Remarks:</strong> {$remarks}
            </p>
            HTML,
            $shouldShowCreditHours =>
            <<<HTML
            <p>
                <strong>Total:</strong> {$totalHours} Hours
            </p>
            <p>
                <strong>Credit:</strong> {$creditHours} Hours
            </p>
            HTML,
            $shouldShowRemarks =>
            <<<HTML
            <p>
                <strong>Total:</strong> {$totalHours} Hours
            </p>
            <p>
                <strong>Remarks:</strong> {$remarks}
            </p>
            HTML,
            default =>
            <<<HTML
            <p>
                <strong>Total:</strong> {$totalHours} Hours
            </p>
            HTML
        };
    }

    /**
     * @param TimeEntry[] $timeEntries
     */
    private function getContext(
        array $timeEntries,
        MonthFilters $monthFilters,
        RemarksForMonth| NotFound $remarks,
        CreditHours|NotFound $creditHours,
    ): TimeEntriesContext {
        return match (true) {
            $remarks instanceof NotFound && $creditHours instanceof NotFound =>
            new TimeEntriesContext(
                timeEntries: $timeEntries,
                filters: $monthFilters,
                user: $this->user
            ),
            $creditHours instanceof CreditHours && $remarks instanceof NotFound =>
            new TimeEntriesContext(
                timeEntries: $timeEntries,
                filters: $monthFilters,
                creditHours: $creditHours,
                user: $this->user
            ),
            $creditHours instanceof CreditHours && $remarks instanceof RemarksForMonth =>
            new TimeEntriesContext(
                timeEntries: $timeEntries,
                filters: $monthFilters,
                remarks: $remarks,
                creditHours: $creditHours,
                user: $this->user,
            ),
            $remarks instanceof RemarksForMonth && $creditHours instanceof NotFound =>
            new TimeEntriesContext(
                timeEntries: $timeEntries,
                filters: $monthFilters,
                remarks: $remarks,
                user: $this->user
            ),
            default =>
            new TimeEntriesContext(user: $this->user)
        };
    }
}
