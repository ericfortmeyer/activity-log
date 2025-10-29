<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

/**
 * Context for the single time entry view
 */
final class TimeEntryContext
{
    public function __construct(
        public TimeEntry $timeEntry,
        private readonly string $title = "Activity Details",
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
