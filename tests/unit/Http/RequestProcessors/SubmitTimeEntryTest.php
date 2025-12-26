<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use EricFortmeyer\ActivityLog\MonthFilters;
use EricFortmeyer\ActivityLog\RemarksForMonth;
use EricFortmeyer\ActivityLog\Services\RemarksForMonthService;
use EricFortmeyer\ActivityLog\Services\TemplateBinder;
use EricFortmeyer\ActivityLog\Services\TimeEntryService;
use EricFortmeyer\ActivityLog\TimeEntry;
use EricFortmeyer\ActivityLog\UnitTests\DataProviders\TimeEntryDataProvider;
use EricFortmeyer\ActivityLog\Utils\Hasher;
use Phpolar\Phpolar\Auth\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\Storage\NotFound;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\MockObject\Stub;

#[CoversClass(SubmitTimeEntry::class)]
#[CoversClass(TimeEntry::class)]
#[CoversClass(MonthFilters::class)]
#[CoversClass(RemarksForMonth::class)]
final class SubmitTimeEntryTest extends TestCase
{
    private TemplateBinder $templateEngine;
    private RemarksForMonthService&Stub $remarksForMonthService;

    protected function setUp(): void
    {
        $this->templateEngine = new TemplateBinder(new TemplateEngine());
        $this->remarksForMonthService = $this->createStub(RemarksForMonthService::class);
        $this->remarksForMonthService
            ->method("get")
            ->willReturn(new NotFound());
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
        $hasher = $this->createStub(Hasher::class);
        $timeEntryService = $this->createMock(TimeEntryService::class);
        $submitTimeEntry = new SubmitTimeEntry(
            appVersion: "",
            timeEntryService: $timeEntryService,
            remarksForMonthService: $this->remarksForMonthService,
            templateEngine: $this->templateEngine,
            hasher: $hasher,
        );
        $submitTimeEntry->user = new User(
            name: "FAKE_NAME",
            nickname: "FAKE_NICKNAME",
            email: "FAKE@FAKE.FAKE",
            avatarUrl: "https://FAKE.FAKE/FAKE.png",
        );
        $timeEntryService->expects($this->once())
            ->method("save")
            ->with($this->isInstanceOf(TimeEntry::class));

        $response = $submitTimeEntry->process($entry);
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

        $hasher = $this->createStub(Hasher::class);
        $timeEntryService = $this->createMock(TimeEntryService::class);
        $submitTimeEntry = new SubmitTimeEntry(
            appVersion: "",
            timeEntryService: $timeEntryService,
            remarksForMonthService: $this->remarksForMonthService,
            templateEngine: $this->templateEngine,
            hasher: $hasher,
        );
        $submitTimeEntry->user = new User(
            name: "FAKE_NAME",
            nickname: "FAKE_NICKNAME",
            email: "FAKE@FAKE.FAKE",
            avatarUrl: "https://FAKE.FAKE/FAKE.png",
        );
        $timeEntryService->expects($this->never())
            ->method("save");

        $response = $submitTimeEntry->process($entry);
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

        $hasher = $this->createStub(Hasher::class);
        $timeEntryService = $this->createStub(TimeEntryService::class);
        $submitTimeEntry = new SubmitTimeEntry(
            appVersion: "",
            timeEntryService: $timeEntryService,
            remarksForMonthService: $this->remarksForMonthService,
            templateEngine: $this->templateEngine,
            hasher: $hasher,
        );
        $submitTimeEntry->user = new User(
            name: "FAKE_NAME",
            nickname: "FAKE_NICKNAME",
            email: "FAKE@FAKE.FAKE",
            avatarUrl: "https://FAKE.FAKE/FAKE.png",
        );

        $response = $submitTimeEntry->process($entry);
        $this->assertStringContainsString("Activity", $response);
    }
}
