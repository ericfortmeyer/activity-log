<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use EricFortmeyer\ActivityLog\Services\TemplateBinder;
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
        private readonly TemplateBinder $templateEngine,
    ) {}

    #[Authorize]
    public function process(string $id = ""): string
    {
        $timeEntry = $this->timeEntryService->get($id);

        if ($timeEntry instanceof NotFound) {
            return $this->templateEngine->apply(
                "404",
                new NotFoundContext("The requested time entry was not found.")
            );
        }

        return $this->templateEngine->apply(
            "entry",
            new TimeEntryContext(timeEntry: $timeEntry)
        );
    }
}
