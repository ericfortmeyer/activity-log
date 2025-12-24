<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use EricFortmeyer\ActivityLog\DI\ValueProvider;
use Exception;
use Psr\Container\ContainerInterface;

return [
    TenantService::class => static fn(ContainerInterface $container)
    => new TenantService(
        connection: new ServiceProvider($container)->appDataConnection,
    ),
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
    DataExportService::class => static function (ContainerInterface $container) {
        $csv = fopen("php://memory", "+w");
        if ($csv === false) {
            $json = json_encode(error_get_last());
            throw new Exception($json === false ? "" : $json);
        }
        return new DataExportService(
            storageContext: new ServiceProvider($container)->timeEntryStorage,
            csv: $csv,
        );
    },
];
