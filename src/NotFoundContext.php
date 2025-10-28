<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

/**
 * Context for the not found error page
 */
final class NotFoundContext
{
    private string $title = "Not Found";
    public function __construct(
        public string $message,
    ) {}

    public function getTitle(): string
    {
        return $this->title;
    }
}
