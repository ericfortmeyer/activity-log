<?php

/**
 * @phan-file-suppress PhanUnreferencedClosure
 */

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use Psr\Container\ContainerInterface;

use const EricFortmeyer\ActivityLog\DI\Tokens\BOOTSTRAPPER;

return [
    BOOTSTRAPPER => static fn(ContainerInterface $container) => new Bootstrapper(
        container: $container,
        serverRequest: new ServiceProvider($container)->serverRequest,
        callbackMiddleware: new ServiceProvider($container)->callbackMiddleware,
        loginMiddleware: new ServiceProvider($container)->loginMiddleware,
        logoutMiddleware: new ServiceProvider($container)->logoutMiddleware,
        exceptionHandler: new ServiceProvider($container)->exceptionHandler,
    ),
];
