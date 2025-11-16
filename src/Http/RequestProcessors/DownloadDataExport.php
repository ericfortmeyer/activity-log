<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use EricFortmeyer\ActivityLog\Services\DataExportService;
use Phpolar\HttpRequestProcessor\RequestProcessorInterface;
use Phpolar\Phpolar\Auth\Authorize;

final class DownloadDataExport implements RequestProcessorInterface
{
    public function __construct(private DataExportService $dataExportService) {}

    #[Authorize]
    public function process(): string
    {
        return $this->dataExportService->export();
    }
}
