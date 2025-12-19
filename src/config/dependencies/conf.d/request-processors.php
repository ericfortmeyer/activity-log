<?php

/**
 * @phan-file-suppress PhanUnreferencedClosure
 */

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use Psr\Container\ContainerInterface;

return [
    GetTimeEntries::class => static fn(ContainerInterface $container) => new GetTimeEntries(
        timeEntryService: new ServiceProvider($container)->timeEntryService,
        remarksForMonthService: new ServiceProvider($container)->remarksForMonthService,
        creditHoursService: new ServiceProvider($container)->creditHoursService,
        templateEngine: new ServiceProvider($container)->templateEngine,
        hasher: new ServiceProvider($container)->hasher,
    ),
    GetTimeEntry::class => static fn(ContainerInterface $container) => new GetTimeEntry(
        new ServiceProvider($container)->timeEntryService,
        new ServiceProvider($container)->templateEngine,
    ),
    DeleteTimeEntry::class => static fn(ContainerInterface $container) => new DeleteTimeEntry(
        new ServiceProvider($container)->timeEntryService,
        new ServiceProvider($container)->remarksForMonthService,
        new ServiceProvider($container)->templateEngine,
        new ServiceProvider($container)->hasher,
    ),
    SubmitTimeEntry::class => static fn(ContainerInterface $container) => new SubmitTimeEntry(
        new ServiceProvider($container)->timeEntryService,
        new ServiceProvider($container)->remarksForMonthService,
        new ServiceProvider($container)->templateEngine,
        new ServiceProvider($container)->hasher,
    ),
    SaveRemarksForMonth::class => static fn(ContainerInterface $container) => new SaveRemarksForMonth(
        creditHoursService: new ServiceProvider($container)->creditHoursService,
        remarksService: new ServiceProvider($container)->remarksForMonthService,
        timeEntryService: new ServiceProvider($container)->timeEntryService,
        templateEngine: new ServiceProvider($container)->templateEngine,
        hasher: new ServiceProvider($container)->hasher,
    ),
    SaveCreditHours::class => static fn(ContainerInterface $container) => new SaveCreditHours(
        creditHoursService: new ServiceProvider($container)->creditHoursService,
        remarksService: new ServiceProvider($container)->remarksForMonthService,
        timeEntryService: new ServiceProvider($container)->timeEntryService,
        templateEngine: new ServiceProvider($container)->templateEngine,
        hasher: new ServiceProvider($container)->hasher,
    ),
    DownloadDataExport::class => static fn(ContainerInterface $container) => new DownloadDataExport(
        new ServiceProvider($container)->dataExportService,
    ),
    EmailReportForMonth::class => static fn(ContainerInterface $container) => new EmailReportForMonth(
        mailConfig: new ServiceProvider($container)->emailConfig,
        timeEntryService: new ServiceProvider($container)->timeEntryService,
        remarksService: new ServiceProvider($container)->remarksForMonthService,
        creditHoursService: new ServiceProvider($container)->creditHoursService,
        templateEngine: new ServiceProvider($container)->templateEngine,
        hasher: new ServiceProvider($container)->hasher,
    )
];
