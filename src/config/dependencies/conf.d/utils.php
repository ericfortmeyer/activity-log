<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Utils;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use EricFortmeyer\ActivityLog\DI\ValueProvider;
use Psr\Container\ContainerInterface;

return [
    Hasher::class => static fn(ContainerInterface $container) => new Hasher(
        hashingKey: new ServiceProvider($container)->secretsClient->getValue(
            new ValueProvider()->appHashKey,
        ),
        signingKey: new ServiceProvider($container)->secretsClient->getValue(
            new ValueProvider()->appReleaseHookSecretKey,
        ),
    ),
];
