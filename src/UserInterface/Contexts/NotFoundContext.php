<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UserInterface\Contexts;

/**
 * Context for the not found error page
 */
final class NotFoundContext extends AbstractContext
{
    public function __construct(
        public string $message,
    ) {
        parent::__construct(title: "Not Found");
    }
}
