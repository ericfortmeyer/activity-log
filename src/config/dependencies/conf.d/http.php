<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\{
    RequestFactoryInterface,
    ResponseFactoryInterface,
    StreamFactoryInterface
};

return array_combine([
    RequestFactoryInterface::class,
    ResponseFactoryInterface::class,
    StreamFactoryInterface::class,
    Psr17Factory::class,
], array_fill(0, 4, new Psr17Factory()));
