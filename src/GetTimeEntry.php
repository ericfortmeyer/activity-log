<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\HttpRequestProcessor\RequestProcessorInterface;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\PurePhp\HtmlSafeContext;
use EricFortmeyer\ActivityLog\{NotFoundContext, TimeEntryContext};
use Phpolar\Storage\NotFound;

final class GetTimeEntry implements RequestProcessorInterface
{
    public function __construct(
        private readonly TimeEntryService $timeEntryService,
        private readonly TemplateEngine $templateEngine,
    ) {
    }

    public function process(string $id = ""): string
    {
        $timeEntry = $this->timeEntryService->get($id);

        if ($timeEntry instanceof NotFound) {
            return (string) $this->templateEngine->apply(
                "404",
                new HtmlSafeContext(new NotFoundContext("The requested time entry was not found."))
            );
        }

        return (string) $this->templateEngine->apply(
            "entry",
            new HtmlSafeContext(new TimeEntryContext($timeEntry))
        );
    }
}
