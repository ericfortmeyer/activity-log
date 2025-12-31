<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use DateTimeImmutable;
use EricFortmeyer\ActivityLog\TimeEntry;
use Phpolar\Storage\StorageContext;

class DataExportService
{
    /**
     * @param StorageContext<TimeEntry> $storageContext
     * @param resource $csv
     */
    public function __construct(
        private readonly StorageContext $storageContext,
        private $csv,
    ) {}

    public function __destruct()
    {
        fclose($this->csv);
    }

    public function export(): string|false
    {
        $timeEntries = $this->storageContext->findAll();

        foreach ($timeEntries as $timeEntry) {
            fputcsv(
                stream: $this->csv,
                fields: array_map(
                    $this->convertToString(...),
                    get_object_vars($timeEntry),
                ),
                escape: "\\",
            );
        }
        rewind($this->csv);
        return stream_get_contents($this->csv);
    }

    private function convertToString(int|string|DateTimeImmutable $prop): string
    {
        return match (true) {
            $prop instanceof DateTimeImmutable => $prop->format(DATE_ATOM),
            is_int($prop) => (string) $prop,
            default => $prop,
        };
    }
}
