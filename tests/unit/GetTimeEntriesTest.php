<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\Storage\NotFound;

#[CoversClass(GetTimeEntries::class)]
#[CoversClass(TimeEntriesContext::class)]
#[UsesClass(TimeEntry::class)]
final class GetTimeEntriesTest extends TestCase
{
    private TimeEntryService&MockObject $timeEntryService;
    private RemarksForMonthService&MockObject $remarksForMonthService;
    private TemplateEngine $templateEngine;
    private GetTimeEntries $getTimeEntries;

    protected function setUp(): void
    {
        $this->timeEntryService = $this->createMock(TimeEntryService::class);
        $this->remarksForMonthService = $this->createMock(RemarksForMonthService::class);
        $this->templateEngine = new TemplateEngine();
        $this->getTimeEntries = new GetTimeEntries(
            $this->timeEntryService,
            $this->remarksForMonthService,
            $this->templateEngine
        );
    }

    public function testProcessRendersTemplateWithTimeEntries(): void
    {
        $entry1 = new TimeEntry();
        $entry1->id = "test-id-1";
        $entry1->month = 1;
        $entry1->dayOfMonth = 1;
        $entry1->year = 2025;
        $entry1->hours = 8;
        $entry1->minutes = 30;

        $entry2 = new TimeEntry();
        $entry2->id = "test-id-2";
        $entry2->month = 2;
        $entry2->dayOfMonth = 2;
        $entry2->year = 2025;
        $entry2->hours = 6;
        $entry2->minutes = 45;

        $entries = [$entry1, $entry2];

        $this->timeEntryService->expects($this->once())
            ->method("getAllByMonth")
            ->willReturn($entries);

        $this->remarksForMonthService->expects($this->once())
            ->method("get")
            ->willReturn(new NotFound());

        $response = $this->getTimeEntries->process();
        $this->assertStringContainsString("Activity", $response);
    }

    public function testProcessRendersTemplateWithEmptyEntries(): void
    {
        $this->timeEntryService->expects($this->once())
            ->method("getAllByMonth")
            ->willReturn([]);

        $this->remarksForMonthService->expects($this->once())
            ->method("get")
            ->willReturn(new NotFound());

        $response = $this->getTimeEntries->process();
        $this->assertStringContainsString("Activity", $response);
    }
}
