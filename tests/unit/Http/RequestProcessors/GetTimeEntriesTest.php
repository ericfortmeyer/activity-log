<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use EricFortmeyer\ActivityLog\CreditHours;
use EricFortmeyer\ActivityLog\MonthFilters;
use EricFortmeyer\ActivityLog\RemarksForMonth;
use EricFortmeyer\ActivityLog\Services\CreditHoursService;
use EricFortmeyer\ActivityLog\Services\RemarksForMonthService;
use EricFortmeyer\ActivityLog\Services\TimeEntryService;
use EricFortmeyer\ActivityLog\TimeEntry;
use EricFortmeyer\ActivityLog\UserInterface\Contexts\TimeEntriesContext;
use EricFortmeyer\ActivityLog\Utils\Hasher;
use Phpolar\Phpolar\Auth\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\Storage\NotFound;

#[CoversClass(GetTimeEntries::class)]
#[CoversClass(TimeEntriesContext::class)]
#[CoversClass(TimeEntry::class)]
#[CoversClass(MonthFilters::class)]
#[CoversClass(RemarksForMonth::class)]
#[CoversClass(CreditHours::class)]
final class GetTimeEntriesTest extends TestCase
{
    private TimeEntryService&MockObject $timeEntryService;
    private RemarksForMonthService&MockObject $remarksForMonthService;
    private CreditHoursService&MockObject $creditHoursService;
    private TemplateEngine $templateEngine;
    private GetTimeEntries $getTimeEntries;

    protected function setUp(): void
    {
        $hasher = $this->createStub(Hasher::class);
        $this->timeEntryService = $this->createMock(TimeEntryService::class);
        $this->remarksForMonthService = $this->createMock(RemarksForMonthService::class);
        $this->creditHoursService = $this->createMock(CreditHoursService::class);
        $this->templateEngine = new TemplateEngine();
        $this->getTimeEntries = new GetTimeEntries(
            timeEntryService: $this->timeEntryService,
            remarksForMonthService: $this->remarksForMonthService,
            creditHoursService: $this->creditHoursService,
            templateEngine: $this->templateEngine,
            hasher: $hasher,
        );
        $this->getTimeEntries->user = new User(
            name: "",
            nickname: "",
            email: "",
            avatarUrl: "",
        );
    }

    public function testProcessRendersTemplateWithTimeEntries(): void
    {
        $entry1 = new TimeEntry();
        $entry1->id = "test-id-1";
        $entry1->month = 1;
        $entry1->dayOfMonth = 1;
        $entry1->year = "2025";
        $entry1->hours = 8;
        $entry1->minutes = 30;

        $entry2 = new TimeEntry();
        $entry2->id = "test-id-2";
        $entry2->month = 2;
        $entry2->dayOfMonth = 2;
        $entry2->year = "2025";
        $entry2->hours = 6;
        $entry2->minutes = 45;

        $entries = [$entry1, $entry2];

        $this->timeEntryService->expects($this->once())
            ->method("getAllByMonth")
            ->willReturn($entries);

        $this->remarksForMonthService->expects($this->once())
            ->method("get")
            ->willReturn(new NotFound());
        $this->creditHoursService->expects($this->once())
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
        $this->creditHoursService->expects($this->once())
            ->method("get")
            ->willReturn(new NotFound());

        $response = $this->getTimeEntries->process();
        $this->assertStringContainsString("Activity", $response);
    }
}
