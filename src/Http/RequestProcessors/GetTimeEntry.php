<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use Phpolar\PurePhp\TemplateEngine;
use Phpolar\PurePhp\HtmlSafeContext;
use Phpolar\Phpolar\Auth\{
    AbstractRestrictedAccessRequestProcessor,
    Authorize
};
use Phpolar\Storage\NotFound;
use EricFortmeyer\ActivityLog\Services\TimeEntryService;
use EricFortmeyer\ActivityLog\UserInterface\Contexts\{NotFoundContext, TimeEntryContext};

final class GetTimeEntry extends AbstractRestrictedAccessRequestProcessor
{
    public function __construct(
        private readonly TimeEntryService $timeEntryService,
        private readonly TemplateEngine $templateEngine,
    ) {}

    #[Authorize]
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
            new HtmlSafeContext(new TimeEntryContext(timeEntry: $timeEntry))
        );
    }
}
