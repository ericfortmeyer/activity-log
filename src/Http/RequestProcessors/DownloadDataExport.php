<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use DateTimeImmutable;
use EricFortmeyer\ActivityLog\Services\DataExportService;
use EricFortmeyer\ActivityLog\Services\TemplateBinder;
use EricFortmeyer\ActivityLog\UserInterface\Contexts\ServerErrorContext;
use Phpolar\HttpRequestProcessor\RequestProcessorInterface;
use Phpolar\Phpolar\Auth\Authorize;

final class DownloadDataExport implements RequestProcessorInterface
{
    public function __construct(
        private readonly DataExportService $dataExportService,
        private readonly TemplateBinder $templateEngine
    ) {}

    #[Authorize]
    public function process(): string
    {
        $result = $this->dataExportService->export();

        if ($result === false) {
            return $this->templateEngine->apply(
                "500",
                new ServerErrorContext(
                    message: "An error occurred exporting the data."
                )
            );
        }

        $fileName = join(
            "",
            [
                "export",
                new DateTimeImmutable()->format(DATE_ATOM),
                ".csv",
            ]
        );
        header(sprintf("Content-Disposition: attachment; filename=%s;", $fileName));

        return $result;
    }
}
