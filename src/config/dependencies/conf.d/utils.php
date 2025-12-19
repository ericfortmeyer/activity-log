<?php

/**
 * @phan-file-suppress PhanUnreferencedClosure
 */

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Utils;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use Psr\Container\ContainerInterface;

return [
    Hasher::class => static fn(ContainerInterface $container) => new Hasher(
        hashingKey: new ServiceProvider($container)->secretsClient->getValue("hash-key"),
    ),
];
