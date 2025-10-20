<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

/**
 * Context for the not found error page
 */
final class NotFoundContext
{
    public string $title = "Not Found";
    public function __construct(
        public string $message,
    ) {}
}
