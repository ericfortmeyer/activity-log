<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => static function (ContainerInterface $container) {
        /**
         * @var AppConfig
         */
        $appConfig = $container->get(AppConfig::class);
        return new Logger($appConfig->appName)->pushHandler(
            new SyslogHandler($appConfig->appName)->setFormatter(
                new LineFormatter("%channel%.%level_name%: %message% %context% %extra%")
            )
        );
    },
];
