<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use EricFortmeyer\ActivityLog\Services\TimeEntryService;
use EricFortmeyer\ActivityLog\TimeEntry;
use Phpolar\Phpolar\Auth\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\Storage\NotFound;

#[CoversClass(GetTimeEntry::class)]
#[CoversClass(TimeEntry::class)]
final class GetTimeEntryTest extends TestCase
{
    private TimeEntryService&MockObject $timeEntryService;
    private TemplateEngine $templateEngine;
    private GetTimeEntry $getTimeEntry;

    protected function setUp(): void
    {
        $this->timeEntryService = $this->createMock(TimeEntryService::class);
        $this->templateEngine = new TemplateEngine();
        $this->getTimeEntry = new GetTimeEntry($this->timeEntryService, $this->templateEngine);
        $this->getTimeEntry->user = new User(
            name: "",
            nickname: "",
            email: "",
            avatarUrl: ""
        );
    }

    public function testProcessRendersTemplateWithTimeEntry(): void
    {
        $entryId = "test-id";
        $entry = new TimeEntry();
        $entry->id = $entryId;
        $entry->dayOfMonth = 1;
        $entry->year = "2025";
        $entry->hours = 8;
        $entry->minutes = 30;
        $entry->createdOn = new \DateTimeImmutable();

        $this->timeEntryService->expects($this->once())
            ->method("get")
            ->with($entryId)
            ->willReturn($entry);

        $response = $this->getTimeEntry->process($entryId);
        $this->assertStringContainsString("Activity Details", $response);
    }

    public function testProcessRendersNotFoundTemplateWhenTimeEntryNotFound(): void
    {
        $entryId = "non-existent-id";

        $this->timeEntryService->expects($this->once())
            ->method("get")
            ->with($entryId)
            ->willReturn(new NotFound());

        $response = $this->getTimeEntry->process($entryId);
        $this->assertStringContainsString("Not Found", $response);
    }
}
