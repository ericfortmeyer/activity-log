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
        $log = new Logger($container->get(APP_NAME));
        $syslog = new SyslogHandler($container->get(APP_NAME));
        $formatter = new LineFormatter("%channel%.%level_name%: %message% %context% %extra%");
        $syslog->setFormatter($formatter);
        $log->pushHandler($syslog);
        return $log;
    },
];
