<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use Psr\Container\ContainerInterface;

use const EricFortmeyer\ActivityLog\DI\Tokens\BOOTSTRAPPER;

return [
    BOOTSTRAPPER => static function (ContainerInterface $container): Bootstrapper {
        $serviceProvider = new ServiceProvider($container);
        $shouldRunMigrations = \getenv("ACTIVITY_LOG_RUN_MIGRATIONS");

        return new Bootstrapper(
            container: $container,
            serverRequest: $serviceProvider->serverRequest,
            callbackMiddleware: $serviceProvider->callbackMiddleware,
            loginMiddleware: $serviceProvider->loginMiddleware,
            logoutMiddleware: $serviceProvider->logoutMiddleware,
            migrationRunner: $serviceProvider->migrationRunner,
            shouldRunMigrationsValue: (string) $shouldRunMigrations,
        );
    }
];
