<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use EricFortmeyer\ActivityLog\Services\AppConfigService;
use Phpolar\SqliteStorage\SqliteReadOnlyStorage;
use Psr\Container\ContainerInterface;
use RuntimeException;
use SQLite3;

use const EricFortmeyer\ActivityLog\DI\Tokens\{
    APP_CONFIG_STORAGE,
    APP_DB_CONNECTION,
    DATA_DIR,
};
use const EricFortmeyer\ActivityLog\config\FileNames\APP_DB_STORAGE;

return [
    AppConfig::class => static function (ContainerInterface $container): AppConfig {
        $appConfigResult = new ServiceProvider($container)->appConfigService->get();
        return $appConfigResult === false
            ? throw new RuntimeException("The app config could not be retrieved.")
            : $appConfigResult;
    },
    APP_DB_CONNECTION => static fn(ContainerInterface $container) => new SQLite3(
        filename: join(
            "/",
            [
                $container->get(DATA_DIR),
                APP_DB_STORAGE
            ]
        ),
        flags: \SQLITE3_OPEN_READONLY
    ),
    APP_CONFIG_STORAGE => static fn(ContainerInterface $container): SqliteReadOnlyStorage =>
    new SqliteReadOnlyStorage(
        connection: new ServiceProvider($container)->appConfigConnection,
        tableName: "activity_log_config",
        typeClassName: AppConfig::class,
    ),
    AppConfigService::class => static fn(ContainerInterface $container): AppConfigService =>
    new AppConfigService(
        sqliteStorage: new ServiceProvider($container)->appConfigStorage,
    ),
];
