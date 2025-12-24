<?php

declare(strict_types=1);

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\{
    ServerRequestCreator,
    ServerRequestCreatorInterface
};
use PhpCommonEnums\MimeType\Enumeration\MimeTypeEnum as MimeType;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

return [
    ServerRequestCreatorInterface::class => static fn(ContainerInterface $container) => new ServerRequestCreator(
        ...array_fill(0, 4, $container->get(Psr17Factory::class))
    ),
    ServerRequestInterface::class => static fn(ContainerInterface $container)
    => new ServiceProvider($container)->serverRequestCreator->fromGlobals()
        ->withHeader("Accept", [MimeType::TextHtml->value]),
];
