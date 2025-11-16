<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\ParsedBodyResolver;
use Phpolar\ModelResolver\ModelResolverInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

return [
    ModelResolverInterface::class => static function (ContainerInterface $container) {
        return new ParsedBodyResolver(
            array_merge(
                $container->get(ServerRequestInterface::class)->getQueryParams(),
                $container->get(ServerRequestInterface::class)->getParsedBody() ?? []
            )
        );
    }
];
