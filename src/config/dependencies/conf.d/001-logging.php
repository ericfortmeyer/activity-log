<?php

/**
 * @phan-file-suppress PhanUnreferencedClosure
 */

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use const EricFortmeyer\ActivityLog\DI\Tokens\EXCEPTION_LOGGER;

return [
    LoggerInterface::class => static fn(ContainerInterface $container) =>
    new Logger(
        new ServiceProvider($container)->appConfig->appName
    )->pushHandler(
        new SyslogHandler(
            new ServiceProvider($container)->appConfig->appName
        ),
    ),
    EXCEPTION_LOGGER => static fn(ContainerInterface $container) =>
    new Logger(
        new ServiceProvider($container)->appConfig->appName
    )->pushHandler(
        new SyslogHandler(
            new ServiceProvider($container)->appConfig->appName
        )->setFormatter(
            new LineFormatter("%channel%.%level_name%: %message% %context%")
        )
    )
];
