<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UserInterface\Contexts;

/**
 * Context for the bad request error page
 */
final class BadRequestContext extends AbstractContext
{
    public function __construct(
        public string $message,
    ) {
        parent::__construct(title: "Bad Request");
    }
}
