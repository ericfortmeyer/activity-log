<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\TimeEntry;
use Phpolar\Storage\{
    NotFound,
    StorageContext
};

readonly class TimeEntryService
{
    /**
     * @param StorageContext<TimeEntry> $storageContext
     */
    public function __construct(private StorageContext $storageContext) {}

    /**
     * Deletes a time entry by its ID.
     *
     * @param string $entryId The ID of the time entry to delete.
     * @return TimeEntry|NotFound The deleted TimeEntry object or NotFound if it doesn"t exist.
     */
    public function delete(string $entryId): TimeEntry| NotFound
    {
        /**
         * @var TimeEntry|NotFound $result
         */
        $result = $this->storageContext
            ->remove($entryId)
            ->orElse(static fn() => new NotFound())
            ->tryUnwrap();

        return $result;
    }

    /**
     * Retrieves a time entry by its ID.
     *
     * @param string $entryId The ID of the time entry to retrieve.
     * @return TimeEntry|NotFound The TimeEntry object or NotFound if it doesn"t exist.
     */
    public function get(string $entryId): TimeEntry|NotFound
    {
        /**
         * @var TimeEntry|NotFound $result
         */
        $result = $this->storageContext
            ->find($entryId)
            ->orElse(static fn() => new NotFound())
            ->tryUnwrap();

        return $result;
    }

    /**
     * Retrieves all time entries.
     *
     * @return TimeEntry[] An array of TimeEntry objects.
     */
    public function getAll(string $tenantId): array
    {
        return array_filter(
            $this->storageContext->findAll(),
            TimeEntry::forTenant($tenantId),
        );
    }

    public function save(TimeEntry $entry): void
    {
        if (empty($entry->id) === true) {
            $entry->create();
            $this->storageContext->save($entry->id, $entry);
            return;
        }
        $this->storageContext->replace($entry->id, $entry);
    }

    /**
     * @return TimeEntry[]
     */
    public function getAllByMonth(int $month, string $year, string $tenantId): array
    {
        return array_filter(
            $this->getAll($tenantId),
            TimeEntry::byMonthAndYear($month, $year),
        );
    }
}
