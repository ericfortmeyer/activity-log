<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\DI;

use RuntimeException;

/**
 * @codeCoverageIgnore
 */
final class MissingEnvVariableException extends RuntimeException
{
    public function __construct(string $envVar)
    {
        parent::__construct(
            sprintf("Environment variable %s is missing but is required.", $envVar),
        );
    }
}
