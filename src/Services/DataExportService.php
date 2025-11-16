<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use DateTimeImmutable;

readonly class DataExportService
{
    public function __construct(private string $sourceFileName) {}

    public function export(): string
    {
        $fileName = join(
            "",
            [
                "export",
                new DateTimeImmutable()->format(DATE_ATOM),
                ".csv",
            ]
        );
        header(sprintf("Content-Disposition: attachment; filename=%s;", $fileName));
        return (string) file_get_contents($this->sourceFileName);
    }
}
