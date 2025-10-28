<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\Storage\NotFound;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * Class DeleteTimeEntry
 *
 * @package EricFortmeyer\ActivityLog
 */
#[CoversClass(DeleteTimeEntry::class)]
#[UsesClass(TimeEntriesContext::class)]
#[UsesClass(TimeEntry::class)]
#[UsesClass(NotFoundContext::class)]
final class DeleteTimeEntryTest extends TestCase
{
    private TimeEntryService&MockObject $timeEntryService;
    private RemarksForMonthService&MockObject $remarksForMonthService;
    private TemplateEngine $templateEngine;
    private DeleteTimeEntry $deleteTimeEntry;

    protected function setUp(): void
    {
        $this->timeEntryService = $this->createMock(TimeEntryService::class);
        $this->remarksForMonthService = $this->createMock(RemarksForMonthService::class);
        $this->remarksForMonthService
            ->method("get")
            ->willReturn(new NotFound());
        $this->templateEngine = new TemplateEngine();
        $this->deleteTimeEntry = new DeleteTimeEntry(
            timeEntryService: $this->timeEntryService,
            remarksForMonthService: $this->remarksForMonthService,
            templateEngine: $this->templateEngine,
        );
    }

    public function testProcessDeletesTimeEntry(): void
    {
        $entryId = "12345";
        $deletedEntry = new TimeEntry();
        $deletedEntry->id = $entryId;
        $deletedEntry->dayOfMonth = 1;
        $deletedEntry->year = 2025;
        $deletedEntry->hours = 8;
        $deletedEntry->minutes = 30;
        $deletedEntry->createdOn = new \DateTimeImmutable();

        $this->timeEntryService
            ->expects($this->once())
            ->method("delete")
            ->with($entryId)
            ->willReturn($deletedEntry);

        $response = $this->deleteTimeEntry->process(new MonthFilters(), $entryId);
        $this->assertStringContainsString("Activity", $response);
    }

    public function testProcessHandlesNotFound(): void
    {
        $entryId = "nonexistent";
        $this->timeEntryService
            ->expects($this->once())
            ->method("delete")
            ->willReturn(new NotFound());

        $response = $this->deleteTimeEntry->process(new MonthFilters(), $entryId);
        $this->assertStringContainsString("Not Found", $response);
    }
}
