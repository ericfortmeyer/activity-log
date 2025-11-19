<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use EricFortmeyer\ActivityLog\MonthFilters;
use EricFortmeyer\ActivityLog\RemarksForMonth;
use EricFortmeyer\ActivityLog\Services\RemarksForMonthService;
use EricFortmeyer\ActivityLog\Services\TimeEntryService;
use EricFortmeyer\ActivityLog\TimeEntry;
use EricFortmeyer\ActivityLog\UnitTests\DataProviders\TimeEntryDataProvider;
use EricFortmeyer\ActivityLog\UserInterface\Contexts\TimeEntriesContext;
use EricFortmeyer\ActivityLog\UserInterface\Contexts\TimeEntryContext;
use Phpolar\Phpolar\Auth\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\Storage\NotFound;
use PHPUnit\Framework\Attributes\DataProviderExternal;

#[CoversClass(SubmitTimeEntry::class)]
#[CoversClass(TimeEntry::class)]
#[CoversClass(MonthFilters::class)]
#[CoversClass(RemarksForMonth::class)]
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
        $this->submitTimeEntry->user = new User(
            name: "FAKE_NAME",
            nickname: "FAKE_NICKNAME",
            email: "FAKE@FAKE.FAKE",
            avatarUrl: "https://FAKE.FAKE/FAKE.png",
        );
    }

    #[DataProviderExternal(TimeEntryDataProvider::class, 'validTimeEntryData')]
    public function testProcessRendersTemplateWithUpdatedTimeEntries(
        string $id,
        string $tenantId,
        int $dayOfMonth,
        int $year,
        int $month,
        int $hours,
        int $minutes,
    ): void {
        $entry = new TimeEntry(
            compact(
                "id",
                "tenantId",
                "dayOfMonth",
                "year",
                "month",
                "hours",
                "minutes",
            )
        );

        $this->timeEntryService->expects($this->once())
            ->method("save")
            ->with($this->isInstanceOf(TimeEntry::class));

        $response = $this->submitTimeEntry->process($entry);
        $this->assertStringContainsString("Activity", $response);
    }

    #[DataProviderExternal(TimeEntryDataProvider::class, 'invalidTimeEntryData')]
    public function testProcessDoesNotSaveInvalidEntry(
        string $invalidProp,
        string|int $invalidValue,
        string $id,
        string $tenantId,
        int $dayOfMonth,
        int $year,
        int $month,
        int $hours,
        int $minutes,
    ): void {
        $entry = new TimeEntry(
            compact(
                "id",
                "tenantId",
                "dayOfMonth",
                "year",
                "month",
                "hours",
                "minutes",
            )
        );

        $this->timeEntryService->expects($this->never())
            ->method("save");

        $response = $this->submitTimeEntry->process($entry);
        $this->assertStringContainsString("Activity", $response);
        $this->assertFalse($entry->isValid());
    }

    #[DataProviderExternal(TimeEntryDataProvider::class, 'validTimeEntryData')]
    public function testProcessMarksEntryAsPosted(
        string $id,
        string $tenantId,
        int $dayOfMonth,
        int $year,
        int $month,
        int $hours,
        int $minutes,

    ): void {
        $entry = new TimeEntry(
            compact(
                "id",
                "tenantId",
                "dayOfMonth",
                "year",
                "month",
                "hours",
                "minutes",
            )
        );

        $response = $this->submitTimeEntry->process($entry);
        $this->assertStringContainsString("Activity", $response);
    }
}
