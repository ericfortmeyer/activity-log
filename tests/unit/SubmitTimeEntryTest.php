<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\Storage\NotFound;

#[CoversClass(SubmitTimeEntry::class)]
#[UsesClass(TimeEntry::class)]
#[CoversClass(TimeEntryContext::class)]
#[CoversClass(TimeEntriesContext::class)]
final class SubmitTimeEntryTest extends TestCase
{
    private TimeEntryService&MockObject $timeEntryService;
    private TemplateEngine $templateEngine;
    private SubmitTimeEntry $submitTimeEntry;
    private RemarksForMonthService&MockObject $remarksForMonthService;

    protected function setUp(): void
    {
        $this->timeEntryService = $this->createMock(TimeEntryService::class);
        $this->remarksForMonthService = $this->createMock(RemarksForMonthService::class);
        $this->remarksForMonthService
            ->method("get")
            ->willReturn(new NotFound());
        $this->templateEngine = new TemplateEngine();
        $this->submitTimeEntry = new SubmitTimeEntry(
            timeEntryService: $this->timeEntryService,
            remarksForMonthService: $this->remarksForMonthService,
            templateEngine: $this->templateEngine,
        );
    }

    public function testProcessRendersTemplateWithUpdatedTimeEntries(): void
    {
        $entry = new TimeEntry();
        $entry->dayOfMonth = 1;
        $entry->year = 2025;
        $entry->hours = 8;
        $entry->minutes = 30;
        $entry->createdOn = new \DateTimeImmutable();

        $this->timeEntryService->expects($this->once())
            ->method("save")
            ->with($this->isInstanceOf(TimeEntry::class));

        $response = $this->submitTimeEntry->process($entry);
        $this->assertStringContainsString("Activity", $response);
    }

    public function testProcessDoesNotSaveInvalidEntry(): void
    {
        $entry = new TimeEntry();
        // year must be current year
        $entry->year = 2020;
        $entry->dayOfMonth = 1;
        $entry->hours = 8;
        $entry->minutes = 30;
        $entry->createdOn = new \DateTimeImmutable();

        $this->timeEntryService->expects($this->never())
            ->method("save");

        $response = $this->submitTimeEntry->process($entry);
        $this->assertStringContainsString("Activity", $response);
        $this->assertFalse($entry->isValid());
        $this->assertFalse(isset($entry->id), "ID should not be set for invalid entries");
    }

    public function testProcessMarksEntryAsPosted(): void
    {
        $entry = new TimeEntry();
        $entry->dayOfMonth = 1;
        $entry->year = 2025;
        $entry->hours = 8;
        $entry->minutes = 30;

        $response = $this->submitTimeEntry->process($entry);
        $this->assertStringContainsString("Activity", $response);
    }
}
