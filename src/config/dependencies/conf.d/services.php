<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\EmailConfig;
use Psr\Container\ContainerInterface;

use const EricFortmeyer\ActivityLog\config\DiTokens\{
    CREDIT_HOURS_STORAGE,
    MAIL_CONFIG,
    REMARKS_STORAGE,
    TIME_ENTRY_CSV_FILE,
    TIME_ENTRY_STORAGE
};

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
    EmailConfig::class => static fn(ContainerInterface $container) => new EmailConfig(
        headers: $container->get(MAIL_CONFIG),
    ),
];
