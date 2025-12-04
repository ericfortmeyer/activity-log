<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use EricFortmeyer\ActivityLog\EmailConfig;
use Psr\Container\ContainerInterface;

return [
    TimeEntryService::class => static fn(ContainerInterface $container)
    => new TimeEntryService(new ServiceProvider($container)->timeEntryStorage),
    RemarksForMonthService::class => static fn(ContainerInterface $container)
    => new RemarksForMonthService(new ServiceProvider($container)->remarksStorage),
    CreditHoursService::class => static fn(ContainerInterface $container)
    => new CreditHoursService(new ServiceProvider($container)->creditHoursStorage),
    EmailConfig::class => new EmailConfig(
        headers: [
            "MIME-Version" => "1.0",
            "Content-Type" => "text/html; charset=iso-8859-1"
        ],
    ),
];
