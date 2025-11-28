<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Psr\Container\ContainerInterface;

use const EricFortmeyer\ActivityLog\config\DiTokens\{
    BOOTSTRAPPER,
};

return [
    BOOTSTRAPPER => static fn(ContainerInterface $container): Bootstrapper => new Bootstrapper($container),
];
