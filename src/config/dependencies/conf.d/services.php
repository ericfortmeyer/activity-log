<?php

/**
 * @phan-file-suppress PhanUnreferencedClosure
 */

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use EricFortmeyer\ActivityLog\DI\ValueProvider;
use EricFortmeyer\ActivityLog\EmailConfig;
use Psr\Container\ContainerInterface;

return [
    TimeEntryService::class => static fn(ContainerInterface $container)
    => new TimeEntryService(new ServiceProvider($container)->timeEntryStorage),
    RemarksForMonthService::class => static fn(ContainerInterface $container)
    => new RemarksForMonthService(new ServiceProvider($container)->remarksStorage),
    CreditHoursService::class => static fn(ContainerInterface $container)
    => new CreditHoursService(new ServiceProvider($container)->creditHoursStorage),
    ActivityLogDbConfigService::class => static fn(ContainerInterface $container) => new ActivityLogDbConfigService(
        appUser: new ValueProvider()->appUser,
        host: new ValueProvider()->dbHost,
        databaseName: new ValueProvider()->dbName,
        secretsClient: new ServiceProvider($container)->secretsClient,
        secretKey: new ValueProvider()->dbPasswdStoreKey,
    ),
    DataExportService::class => new DataExportService(""),
    EmailConfig::class => new EmailConfig(
        headers: [
            "MIME-Version" => "1.0",
            "Content-Type" => "text/html; charset=iso-8859-1"
        ],
    ),
];
