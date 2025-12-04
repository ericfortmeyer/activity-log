<?php

declare(strict_types=1);

use EricFortmeyer\ActivityLog\{
    CreditHours,
    RemarksForMonth,
    TimeEntry
};
use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use Pdo\Mysql;
use Phpolar\MySqlStorage\MySqlStorage;
use Phpolar\Storage\StorageContext;
use Psr\Container\ContainerInterface;

use const EricFortmeyer\ActivityLog\DI\Tokens\{
    APP_DB_CONNECTION,
    CREDIT_HOURS_STORAGE as DiTokensCREDIT_HOURS_STORAGE,
    MIGRATION_RUNNER_DB_CONNECTION,
    REMARKS_STORAGE as DiTokensREMARKS_STORAGE,
    TIME_ENTRY_STORAGE as DiTokensTIME_ENTRY_STORAGE,
};

return [
    DiTokensTIME_ENTRY_STORAGE => static fn(ContainerInterface $container): StorageContext =>
    new MySqlStorage(
        connection: new ServiceProvider($container)->appDataConnection,
        tableName: new TimeEntry()->getName(),
        typeClassName: TimeEntry::class,
    ),
    DiTokensCREDIT_HOURS_STORAGE => static fn(ContainerInterface $container)
    =>
    new MySqlStorage(
        connection: new ServiceProvider($container)->appDataConnection,
        tableName: new CreditHours()->getName(),
        typeClassName: CreditHours::class,
    ),
    DiTokensREMARKS_STORAGE => static fn(ContainerInterface $container)
    => new MySqlStorage(
        connection: new ServiceProvider($container)->appDataConnection,
        tableName: new RemarksForMonth()->getName(),
        typeClassName: RemarksForMonth::class,
    ),
    APP_DB_CONNECTION => static function (ContainerInterface $container): Mysql {
        $serviceProvider = new ServiceProvider($container);
        $dbConfigService = $serviceProvider->dbConfigService;
        $logger = $serviceProvider->logger;

        $logger->info("Creating DB connection.");

        return new Mysql(
            dsn: $dbConfigService->getDsn(),
            username: $dbConfigService->getAppUser(),
            password: $dbConfigService->getAppPassword(),
        );
    },
    MIGRATION_RUNNER_DB_CONNECTION => static function (ContainerInterface $container): Mysql {
        $serviceProvider = new ServiceProvider($container);
        $dbConfigService = $serviceProvider->dbConfigService;
        $logger = $serviceProvider->logger;

        $logger->info("Creating migration runner DB connection.");

        return new Mysql(
            dsn: $dbConfigService->getDsn(),
            username: $dbConfigService->getMigrationRunnerUser(),
            password: $dbConfigService->getMigrationRunnerPassword(),
        );
    },
];
