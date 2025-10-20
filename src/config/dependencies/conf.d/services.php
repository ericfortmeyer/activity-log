<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Psr\Container\ContainerInterface;

use const EricFortmeyer\ActivityLog\DiTokens\CREDIT_HOURS_STORAGE;
use const EricFortmeyer\ActivityLog\DiTokens\MAIL_CONFIG;
use const EricFortmeyer\ActivityLog\DiTokens\REMARKS_STORAGE;
use const EricFortmeyer\ActivityLog\DiTokens\TIME_ENTRY_CSV_FILE;
use const EricFortmeyer\ActivityLog\DiTokens\TIME_ENTRY_STORAGE;

return [
    TimeEntryService::class => static fn(ContainerInterface $container) => new TimeEntryService(
        $container->get(TIME_ENTRY_STORAGE),
    ),
    RemarksForMonthService::class => static fn(ContainerInterface $container) => new RemarksForMonthService(
        $container->get(REMARKS_STORAGE),
    ),
    CreditHoursService::class => static fn(ContainerInterface $container) => new CreditHoursService(
        $container->get(CREDIT_HOURS_STORAGE),
    ),
    DataExportService::class => static fn(ContainerInterface $container) => new DataExportService(
        $container->get(TIME_ENTRY_CSV_FILE),
    ),
    MailConfigurationService::class => static fn(ContainerInterface $container) => new MailConfigurationService(
        headers: $container->get(MAIL_CONFIG),
    ),
];
