<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Psr\Container\ContainerInterface;
use Phpolar\PurePhp\TemplateEngine;

return [
    GetTimeEntries::class => static fn(ContainerInterface $container) => new GetTimeEntries(
        timeEntryService: $container->get(TimeEntryService::class),
        remarksForMonthService: $container->get(RemarksForMonthService::class),
        creditHoursService: $container->get(CreditHoursService::class),
        templateEngine: $container->get(TemplateEngine::class),
    ),

    GetTimeEntry::class => static fn(ContainerInterface $container) => new GetTimeEntry(
        $container->get(TimeEntryService::class),
        $container->get(TemplateEngine::class),
    ),

    DeleteTimeEntry::class => static fn(ContainerInterface $container) => new DeleteTimeEntry(
        $container->get(TimeEntryService::class),
        $container->get(RemarksForMonthService::class),
        $container->get(TemplateEngine::class),
    ),

    SubmitTimeEntry::class => static fn(ContainerInterface $container) => new SubmitTimeEntry(
        $container->get(TimeEntryService::class),
        $container->get(RemarksForMonthService::class),
        $container->get(TemplateEngine::class),
    ),
    SaveRemarksForMonth::class => static fn(ContainerInterface $container) => new SaveRemarksForMonth(
        creditHoursService: $container->get(CreditHoursService::class),
        remarksService: $container->get(RemarksForMonthService::class),
        timeEntryService: $container->get(TimeEntryService::class),
        templateEngine: $container->get(TemplateEngine::class),
    ),
    SaveCreditHours::class => static fn(ContainerInterface $container) => new SaveCreditHours(
        creditHoursService: $container->get(CreditHoursService::class),
        remarksService: $container->get(RemarksForMonthService::class),
        timeEntryService: $container->get(TimeEntryService::class),
        templateEngine: $container->get(TemplateEngine::class),
    ),
    DownloadDataExport::class => static fn(ContainerInterface $container) => new DownloadDataExport(
        $container->get(DataExportService::class),
    ),
    EmailReportForMonth::class => static fn(ContainerInterface $container) => new EmailReportForMonth(
        mailConfigurationService: $container->get(MailConfigurationService::class),
        timeEntryService: $container->get(TimeEntryService::class),
        remarksService: $container->get(RemarksForMonthService::class),
        creditHoursService: $container->get(CreditHoursService::class),
        templateEngine: $container->get(TemplateEngine::class),
    )
];
