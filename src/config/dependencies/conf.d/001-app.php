<?php

/**
 * @phan-file-suppress PhanUnreferencedClosure
 */

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use EricFortmeyer\ActivityLog\DI\ValueProvider;
use EricFortmeyer\ActivityLog\Services\AppConfigService;
use Phpolar\SqliteStorage\SqliteReadOnlyStorage;
use Psr\Container\ContainerInterface;
use RuntimeException;
use SQLite3;

use const EricFortmeyer\ActivityLog\DI\Tokens\{
    APP_CONFIG_DB_CONNECTION,
    APP_CONFIG_STORAGE,
};

return [
    AppConfig::class => static function (ContainerInterface $container): AppConfig {
        $appConfigResult = new ServiceProvider($container)->appConfigService->get();
        return $appConfigResult === false
            ? throw new RuntimeException("The app config could not be retrieved.")
            : $appConfigResult;
    },
    APP_CONFIG_DB_CONNECTION => new SQLite3(
        filename: new ValueProvider()->appConfigDbFilename,
        flags: \SQLITE3_OPEN_READONLY
    ),
    APP_CONFIG_STORAGE => static fn(ContainerInterface $container): SqliteReadOnlyStorage =>
    new SqliteReadOnlyStorage(
        connection: new ServiceProvider($container)->appConfigConnection,
        tableName: new ValueProvider()->appConfigTableName,
        typeClassName: AppConfig::class,
    ),
    AppConfigService::class => static fn(ContainerInterface $container): AppConfigService =>
    new AppConfigService(
        storage: new ServiceProvider($container)->appConfigStorage,
    ),
];
