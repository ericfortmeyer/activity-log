<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\DI;

use RuntimeException;

/**
 * @codeCoverageIgnore
 */
final class MissingDependencyException extends RuntimeException
{
    public function __construct(string $containerId)
    {
        parent::__construct(
            \sprintf("%s is not set up in the DI container but is required.", $containerId)
        );
    }
}
