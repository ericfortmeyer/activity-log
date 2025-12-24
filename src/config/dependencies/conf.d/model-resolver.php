<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use Phpolar\Model\ParsedBodyResolver;
use Phpolar\ModelResolver\ModelResolverInterface;
use Psr\Container\ContainerInterface;

return [
    ModelResolverInterface::class => static function (ContainerInterface $container) {
        $parsedBody = new ServiceProvider($container)->serverRequest->getParsedBody();

        return new ParsedBodyResolver(
            array_merge(
                new ServiceProvider($container)->serverRequest->getQueryParams(),
                (\is_object($parsedBody)
                    ? \get_object_vars($parsedBody)
                    : (\is_array($parsedBody) ? $parsedBody : [])),
            )
        );
    }
];
