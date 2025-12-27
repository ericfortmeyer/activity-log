<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UserInterface\Contexts;

/**
 * Context for the successful deletion of an account
 */
final class AccountDeleteSuccessContext extends AbstractContext
{
    public function __construct(
        public string $message,
    ) {
        parent::__construct(title: "Account Delete Successful");
    }
}
