<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\PropertyInjectorContract\PropertyInjectorInterface;
use Phpolar\PurePhp\TemplateEngine;
use Psr\Container\ContainerInterface;

final class PropertyInjector implements PropertyInjectorInterface
{
    public function __construct(
        private readonly TemplateEngine $templateEngine,
    ) {}

    /**
     * Inject properties into the given object.
     *
     * @param object $object The object to inject properties into.
     *
     * @return void
     */
    public function inject(object $object): void
    {
        // intentionally left blank
    }
}


return [
    PropertyInjectorInterface::class => static fn(ContainerInterface $container) => new PropertyInjector(
        $container->get(TemplateEngine::class),
    ),
];
