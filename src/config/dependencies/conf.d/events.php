<?php

declare(strict_types=1);

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use EricFortmeyer\ActivityLog\DI\ValueProvider;
use EricFortmeyer\ActivityLog\Http\NotifyReleaseEventMiddleware;
use Psr\Container\ContainerInterface;

return [
    NotifyReleaseEventMiddleware::class => static fn(ContainerInterface $container) =>
    new NotifyReleaseEventMiddleware(
        releaseEventDestination: new ValueProvider()->releaseEventDestination,
        releaseEventHookPath: new ValueProvider()->releaseEventHookPath,
        responseFactory: new ServiceProvider($container)->responseFactory,
        appVersionUpdater: new ServiceProvider($container)->appConfigService,
        verifier: new ServiceProvider($container)->hasher,
    ),
];
