<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Phpolar\DependencyInjection\DiTokens;
use Phpolar\PropertyInjectorContract\PropertyInjectorInterface;
use Psr\Container\ContainerInterface;

return [
    PropertyInjectorInterface::class => static fn(ContainerInterface $container) =>
    $container->get(DiTokens::NOOP_PROPERTY_INJECTOR),
];
