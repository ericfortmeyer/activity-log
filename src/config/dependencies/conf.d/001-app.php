<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\Services\AppConfigService;
use Phpolar\SqliteStorage\SqliteReadOnlyStorage;
use Psr\Container\ContainerInterface;
use RuntimeException;
use SQLite3;

use const EricFortmeyer\ActivityLog\config\DiTokens\{
    APP_CONFIG_SQLITE_STORAGE,
    APP_DB,
    DATA_DIR,
};
use const EricFortmeyer\ActivityLog\config\FileNames\APP_DB_STORAGE;

return [
    AppConfig::class => static function (ContainerInterface $container): AppConfig {
        /**
         * @var AppConfigService
         */
        $appConfigService = $container->get(AppConfigService::class);
        $appConfigResult = $appConfigService->get();

        return $appConfigResult === false
            ? throw new RuntimeException("The app config could not be retrieved.")
            : $appConfigResult;
    },
    APP_DB => static fn(ContainerInterface $container) => new SQLite3(
        filename: join(
            "/",
            [
                $container->get(DATA_DIR),
                APP_DB_STORAGE
            ]
        ),
        flags: \SQLITE3_OPEN_READONLY
    ),
    APP_CONFIG_SQLITE_STORAGE => static function (ContainerInterface $container): SqliteReadOnlyStorage {
        /**
         * @var SQLite3
         */
        $connection = $container->get(APP_DB);
        return new SqliteReadOnlyStorage(
            connection: $connection,
            tableName: "activity_log_config",
            typeClassName: AppConfig::class,
        );
    },
    AppConfigService::class => static function (ContainerInterface $container): AppConfigService {
        /**
         * @var SqliteReadOnlyStorage
         */
        $sqliteStorage = $container->get(APP_CONFIG_SQLITE_STORAGE);
        return new AppConfigService(
            sqliteStorage: $sqliteStorage,
        );
    },
];
