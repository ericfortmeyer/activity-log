<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use EricFortmeyer\ActivityLog\MonthFilters;
use EricFortmeyer\ActivityLog\RemarksForMonth;
use EricFortmeyer\ActivityLog\Services\RemarksForMonthService;
use EricFortmeyer\ActivityLog\Services\TemplateBinder;
use EricFortmeyer\ActivityLog\Services\TimeEntryService;
use EricFortmeyer\ActivityLog\TimeEntry;
use EricFortmeyer\ActivityLog\Utils\Hasher;
use Phpolar\Phpolar\Auth\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\Storage\NotFound;
use PHPUnit\Framework\MockObject\Stub;

#[CoversClass(DeleteTimeEntry::class)]
#[CoversClass(TimeEntry::class)]
#[CoversClass(MonthFilters::class)]
#[CoversClass(RemarksForMonth::class)]
final class DeleteTimeEntryTest extends TestCase
{
    private TimeEntryService&MockObject $timeEntryService;
    private RemarksForMonthService&Stub $remarksForMonthService;
    private TemplateBinder $templateEngine;
    private DeleteTimeEntry $deleteTimeEntry;

    protected function setUp(): void
    {
        $hasher = $this->createStub(Hasher::class);
        $this->timeEntryService = $this->createMock(TimeEntryService::class);
        $this->remarksForMonthService = $this->createStub(RemarksForMonthService::class);
        $this->remarksForMonthService
            ->method("get")
            ->willReturn(new NotFound());
        $this->templateEngine = new TemplateBinder(new TemplateEngine());
        $this->deleteTimeEntry = new DeleteTimeEntry(
            timeEntryService: $this->timeEntryService,
            remarksForMonthService: $this->remarksForMonthService,
            templateEngine: $this->templateEngine,
            hasher: $hasher,
        );
        $this->deleteTimeEntry->user = new User(
            name: "",
            nickname: "",
            email: "",
            avatarUrl: "",
        );
    }

    public function testProcessDeletesTimeEntry(): void
    {
        $entryId = "12345";
        $deletedEntry = new TimeEntry();
        $deletedEntry->id = $entryId;
        $deletedEntry->dayOfMonth = 1;
        $deletedEntry->year = "2025";
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
