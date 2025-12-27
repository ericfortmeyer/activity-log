<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use Psr\Container\ContainerInterface;

return [
    GetTimeEntries::class => static fn(ContainerInterface $container) => new GetTimeEntries(
        appVersion: new ServiceProvider($container)->appConfig->version,
        tenantService: new ServiceProvider($container)->tenantService,
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
    DeleteAllData::class => static fn(ContainerInterface $container) => new DeleteAllData(
        tenantService: new ServiceProvider($container)->tenantService,
        templateEngine: new ServiceProvider($container)->templateEngine,
        hasher: new ServiceProvider($container)->hasher,
    ),
    DeleteTimeEntry::class => static fn(ContainerInterface $container) => new DeleteTimeEntry(
        new ServiceProvider($container)->appConfig->version,
        new ServiceProvider($container)->timeEntryService,
        new ServiceProvider($container)->remarksForMonthService,
        new ServiceProvider($container)->templateEngine,
        new ServiceProvider($container)->hasher,
    ),
    SubmitTimeEntry::class => static fn(ContainerInterface $container) => new SubmitTimeEntry(
        new ServiceProvider($container)->appConfig->version,
        new ServiceProvider($container)->timeEntryService,
        new ServiceProvider($container)->remarksForMonthService,
        new ServiceProvider($container)->templateEngine,
        new ServiceProvider($container)->hasher,
    ),
    SaveRemarksForMonth::class => static fn(ContainerInterface $container) => new SaveRemarksForMonth(
        appVersion: new ServiceProvider($container)->appConfig->version,
        creditHoursService: new ServiceProvider($container)->creditHoursService,
        remarksService: new ServiceProvider($container)->remarksForMonthService,
        timeEntryService: new ServiceProvider($container)->timeEntryService,
        templateEngine: new ServiceProvider($container)->templateEngine,
        hasher: new ServiceProvider($container)->hasher,
    ),
    SaveCreditHours::class => static fn(ContainerInterface $container) => new SaveCreditHours(
        appVersion: new ServiceProvider($container)->appConfig->version,
        creditHoursService: new ServiceProvider($container)->creditHoursService,
        remarksService: new ServiceProvider($container)->remarksForMonthService,
        timeEntryService: new ServiceProvider($container)->timeEntryService,
        templateEngine: new ServiceProvider($container)->templateEngine,
        hasher: new ServiceProvider($container)->hasher,
    ),
    DownloadDataExport::class => static fn(ContainerInterface $container) => new DownloadDataExport(
        new ServiceProvider($container)->dataExportService,
        new ServiceProvider($container)->templateEngine,
    ),
    EmailReportForMonth::class => static fn(ContainerInterface $container) => new EmailReportForMonth(
        appVersion: new ServiceProvider($container)->appConfig->version,
        mailConfig: new ServiceProvider($container)->emailConfig,
        timeEntryService: new ServiceProvider($container)->timeEntryService,
        remarksService: new ServiceProvider($container)->remarksForMonthService,
        creditHoursService: new ServiceProvider($container)->creditHoursService,
        templateEngine: new ServiceProvider($container)->templateEngine,
        hasher: new ServiceProvider($container)->hasher,
    )
];
