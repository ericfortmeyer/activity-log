<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use Phpolar\Model\ParsedBodyResolver;
use Phpolar\ModelResolver\ModelResolverInterface;
use Psr\Container\ContainerInterface;

return [
    ModelResolverInterface::class => static function (ContainerInterface $container) {
        $serverRequest = new ServiceProvider($container)->serverRequest;
        $parsedBody = $serverRequest->getParsedBody();

        return new ParsedBodyResolver(
            array_merge(
                $serverRequest->getQueryParams(),
                (\is_object($parsedBody) === true ? \get_object_vars($parsedBody) : $parsedBody) ?? [],
            )
        );
    }
];
