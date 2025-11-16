<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UserInterface\Contexts;

/**
 * Context for the server error page
 */
final class ServerErrorContext extends AbstractContext
{
    public function __construct(
        public string $message,
    ) {
        parent::__construct(title: "Error");
    }
}
