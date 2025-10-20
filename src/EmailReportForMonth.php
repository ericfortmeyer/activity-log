<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\CreditHours;
use EricFortmeyer\ActivityLog\CreditHoursService;
use EricFortmeyer\ActivityLog\MonthFilters;
use EricFortmeyer\ActivityLog\RemarksForMonth;
use EricFortmeyer\ActivityLog\RemarksForMonthService;
use EricFortmeyer\ActivityLog\TimeEntriesContext;
use EricFortmeyer\ActivityLog\TimeEntryService;
use Phpolar\HttpRequestProcessor\RequestProcessorInterface;
use Phpolar\Model\Model;
use Phpolar\PurePhp\HtmlSafeContext;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\Storage\NotFound;

final class EmailReportForMonth implements RequestProcessorInterface
{
    public function __construct(
        private readonly MailConfigurationService $mailConfigurationService,
        private readonly TimeEntryService $timeEntryService,
        private readonly RemarksForMonthService $remarksService,
        private readonly CreditHoursService $creditHoursService,
        private readonly TemplateEngine $templateEngine,
    ) {}

    public function process(
        #[Model] EmailReportContext $emailReportContext = new EmailReportContext(),
        #[Model] MonthFilters $monthFilters = new MonthFilters(),
    ): array|bool|int|null|object|string {
        $month = $monthFilters->getMonth();
        $year = $monthFilters->getYear();
        $timeEntries = $this->timeEntryService->getAllByMonth(month: $month, year: $year);
        $remarks = $this->remarksService->get(RemarksForMonth::getIdFromMonth(month: $month, year: $year));
        $creditHours = $this->creditHoursService->get(CreditHours::getIdFromMonth(month: $month, year: $year));
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
        EmailReportContext $emailReportContext,
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
            additional_headers: $this->mailConfigurationService->getHeaders(),
        );

        if (!$success) {
            $error = error_get_last()["message"];
            echo $error;
        }
    }

    private function getMessage(
        int $totalHours,
        int $creditHours,
        string $remarks,
        bool $shouldShowCreditHours,
        bool $shouldShowRemarks,
    ): string {
        return "Boom!";
        // return match (true) {
        //     $shouldShowCreditHours && $shouldShowRemarks =>
        //     <<<HTML
        //     <p>
        //         <strong>Total:</strong> {$totalHours} Hours
        //     </p>
        //     <p>
        //         <strong>Credit:</strong> {$creditHours} Hours
        //     </p>
        //     <p>
        //         <strong>Remarks:</strong> {$remarks}
        //     </p>
        //     HTML,
        //     $shouldShowCreditHours =>
        //     <<<HTML
        //     <p>
        //         <strong>Total:</strong> {$totalHours} Hours
        //     </p>
        //     <p>
        //         <strong>Credit:</strong> {$creditHours} Hours
        //     </p>
        //     HTML,
        //     $shouldShowRemarks =>
        //     <<<HTML
        //     <p>
        //         <strong>Total:</strong> {$totalHours} Hours
        //     </p>
        //     <p>
        //         <strong>Remarks:</strong> {$remarks}
        //     </p>
        //     HTML,
        //     default =>
        //     <<<HTML
        //     <p>
        //         <strong>Total:</strong> {$totalHours} Hours
        //     </p>
        //     HTML
        // };
    }

    private function getContext(
        array $timeEntries,
        MonthFilters $monthFilters,
        RemarksForMonth| NotFound $remarks,
        CreditHours|NotFound $creditHours,
    ): TimeEntriesContext {
        return match (true) {
            $remarks instanceof NotFound && $creditHours instanceof NotFound => new TimeEntriesContext(timeEntries: $timeEntries, filters: $monthFilters),
            $creditHours instanceof NotFound === false && $remarks instanceof NotFound === false => new TimeEntriesContext(timeEntries: $timeEntries, filters: $monthFilters, remarks: $remarks, creditHours: $creditHours),
            $creditHours instanceof NotFound === false => new TimeEntriesContext(timeEntries: $timeEntries, filters: $monthFilters, creditHours: $creditHours),
            $remarks instanceof NotFound === false => new TimeEntriesContext(timeEntries: $timeEntries, filters: $monthFilters, remarks: $remarks),
        };
    }
}
