<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Storage\NotFound;
use Phpolar\Storage\StorageContext;

/**
 * Class TimeEntryService
 *
 * @package EricFortmeyer\ActivityLog
 */
class TimeEntryService
{
    public function __construct(private readonly StorageContext $storageContext) {}

    /**
     * Deletes a time entry by its ID.
     *
     * @param string $entryId The ID of the time entry to delete.
     * @return TimeEntry|NotFound The deleted TimeEntry object or NotFound if it doesn"t exist.
     */
    public function delete(string $entryId): TimeEntry| NotFound
    {
        return $this->storageContext
            ->remove($entryId)
            ->orElse(static fn() => new NotFound())
            ->tryUnwrap();
    }

    /**
     * Retrieves a time entry by its ID.
     *
     * @param string $entryId The ID of the time entry to retrieve.
     * @return TimeEntry|NotFound The TimeEntry object or NotFound if it doesn"t exist.
     */
    public function get(string $entryId): TimeEntry|NotFound
    {
        return $this->storageContext
            ->find($entryId)
            ->orElse(static fn() => new NotFound())
            ->tryUnwrap();
    }

    /**
     * Retrieves all time entries.
     *
     * @return array An array of TimeEntry objects.
     */
    public function getAll(): array
    {
        return array_map(
            TimeEntry::fromData(...),
            $this->storageContext->findAll(),
        );
    }

    public function save(TimeEntry $entry): void
    {
        $this->storageContext->save($entry->id, $entry);
    }

    public function getAllByMonth(int $month, int $year): array
    {
        return array_filter(
            $this->getAll(),
            fn(TimeEntry $entry) => $entry->month === $month && $entry->year === $year,
        );
    }
}
