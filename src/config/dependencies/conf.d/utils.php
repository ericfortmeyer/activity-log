<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Utils;

use Psr\Container\ContainerInterface;

use const EricFortmeyer\ActivityLog\config\DiTokens\HASH_KEY;

return [
    Hasher::class => static fn(ContainerInterface $container) => new Hasher(
        hashingKey: $container->get(HASH_KEY)
    )
];
