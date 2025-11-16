<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UserInterface\Contexts;

use EricFortmeyer\ActivityLog\TimeEntry;
use Phpolar\Phpolar\Auth\User;

/**
 * Context for the single time entry view
 */
final class TimeEntryContext extends AbstractContext
{
    public function __construct(
        public TimeEntry $timeEntry,
        public User $user,
    ) {
        parent::__construct(title: "Activity Details");
    }
}
