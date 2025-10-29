<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\HttpRequestProcessor\RequestProcessorInterface;

final class DownloadDataExport implements RequestProcessorInterface
{
    public function __construct(private DataExportService $dataExportService)
    {
    }

    public function process(): array|bool|int|null|object|string
    {
        return $this->dataExportService->export();
    }
}
