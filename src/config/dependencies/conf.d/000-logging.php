<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use const EricFortmeyer\ActivityLog\config\DiTokens\APP_NAME;

return [
    LoggerInterface::class => static function (ContainerInterface $container) {
        return new Logger($container->get(APP_NAME))->pushHandler(
            new SyslogHandler($container->get(APP_NAME))->setFormatter(
                new LineFormatter("%channel%.%level_name%: %message% %context% %extra%")
            )
        );
    },
];
