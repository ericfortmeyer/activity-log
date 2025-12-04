<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => static fn(ContainerInterface $container) =>
    new Logger(
        new ServiceProvider($container)->appConfig->appName
    )->pushHandler(
        new SyslogHandler(
            new ServiceProvider($container)->appConfig->appName
        )->setFormatter(
            new LineFormatter("%channel%.%level_name%: %message% %context% %extra%")
        )
    ),
];
