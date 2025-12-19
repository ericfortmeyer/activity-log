<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\TimeEntry;
use EricFortmeyer\ActivityLog\UnitTests\DataProviders\TimeEntryDataProvider;
use Phpolar\Storage\NotFound;
use Phpolar\Storage\StorageContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeEntryService::class)]
#[UsesClass(TimeEntry::class)]
final class TimeEntryServiceTest extends TestCase
{
    private StorageContext&MockObject $storageContext;
    private TimeEntryService $timeEntryService;

    protected function setUp(): void
    {
        $this->storageContext = $this->createMock(StorageContext::class);
        $this->timeEntryService = new TimeEntryService($this->storageContext);
    }

    #[Test]
    #[TestDox("Shall return the deleted time entry when found")]
    #[TestWith(["test-id"])]
    public function djosif(string $entryId): void
    {
        $expectedEntry = new TimeEntry(["id" => $entryId]);

        $this->storageContext->expects($this->once())
            ->method("remove")
            ->with($entryId)
            ->willReturn(new \Phpolar\Storage\Result($expectedEntry));

        $result = $this->timeEntryService->delete($entryId);

        $this->assertInstanceOf(TimeEntry::class, $result);
        $this->assertSame($entryId, $result->id);
    }

    #[Test]
    #[TestDox("Shall return a not found result when attempting to delete a time entry that is not found")]
    #[TestWith(["non-existent-id"])]
    public function kpodfs(string $nonExistentId): void
    {
        $this->storageContext->expects($this->once())
            ->method("remove")
            ->with($nonExistentId)
            ->willReturn(new \Phpolar\Storage\Result(new NotFound()));

        $result = $this->timeEntryService->delete($nonExistentId);

        $this->assertInstanceOf(NotFound::class, $result);
    }

    #[Test]
    #[TestDox("Shall return the requested time entry when found")]
    #[TestWith(["test-id"])]
    public function fqwjopei(string $entryId): void
    {
        $expectedEntry = new TimeEntry(["id" => $entryId]);

        $this->storageContext->expects($this->once())
            ->method("find")
            ->with($entryId)
            ->willReturn(new \Phpolar\Storage\Result($expectedEntry));

        $result = $this->timeEntryService->get($entryId);

        $this->assertInstanceOf(TimeEntry::class, $result);
        $this->assertSame($entryId, $result->id);
    }

    #[Test]
    #[TestDox("Shall return a not found result when the time entry is not found")]
    #[TestWith(["non-existent-id"])]
    public function testGetReturnsNotFoundWhenEntryDoesNotExist(string $entryId): void
    {
        $this->storageContext->expects($this->once())
            ->method("find")
            ->with($entryId)
            ->willReturn(new \Phpolar\Storage\Result(new NotFound()));

        $result = $this->timeEntryService->get($entryId);

        $this->assertInstanceOf(NotFound::class, $result);
    }

    #[Test]
    #[TestDox("Shall return an array of time entries")]
    #[TestWith([
        "tenantId" => "FAKE_TENANT_ID",
        "entries" => [
            ["tenantId" => "FAKE_TENANT_ID", "id" => "1", "description" => "Test 1"],
            ["tenantId" => "FAKE_TENANT_ID", "id" => "2", "description" => "Test 2"],
        ],
    ])]
    public function adfsijpo(
        string $tenantId,
        array $entries,
    ): void {

        $this->storageContext->expects($this->once())
            ->method("findAll")
            ->willReturn(array_map(static fn(array $it) => new TimeEntry($it), $entries));

        $result = $this->timeEntryService->getAll($tenantId);

        $this->assertIsArray($result);
        $this->assertCount(count($entries), $result);
        $this->assertContainsOnlyInstancesOf(TimeEntry::class, $result);
    }

    #[Test]
    #[TestDox("Shall only return time entries for the given tenant")]
    #[TestWith([
        "tenantId" => "REQUESTED_TENANT",
        "expectedCount" => 3,
        "entries" => [
            ["tenantId" => "REQUESTED_TENANT", "id" => "1", "description" => "Test 1"],
            ["tenantId" => "FAKE_TENANT_ID", "id" => "2", "description" => "Test 2"],
            ["tenantId" => "REQUESTED_TENANT", "id" => "3", "description" => "Test 3"],
            ["tenantId" => "FAKE_TENANT_ID", "id" => "4", "description" => "Test 4"],
            ["tenantId" => "REQUESTED_TENANT", "id" => "5", "description" => "Test 5"],
        ],
    ])]
    #[TestWith([
        "tenantId" => "REQUESTED_TENANT",
        "expectedCount" => 1,
        "entries" => [
            ["tenantId" => "FAKE_TENANT_ID", "id" => "1", "description" => "Test 1"],
            ["tenantId" => "FAKE_TENANT_ID", "id" => "2", "description" => "Test 2"],
            ["tenantId" => "REQUESTED_TENANT", "id" => "3", "description" => "Test 3"],
            ["tenantId" => "FAKE_TENANT_ID", "id" => "4", "description" => "Test 4"],
            ["tenantId" => "FAKE_TENANT_ID", "id" => "5", "description" => "Test 5"],
        ],
    ])]
    public function ajdfsipo(
        string $tenantId,
        int $expectedCount,
        array $entries,
    ): void {

        $this->storageContext->expects($this->once())
            ->method("findAll")
            ->willReturn(array_map(static fn(array $it) => new TimeEntry($it), $entries));

        $result = $this->timeEntryService->getAll($tenantId);

        $this->assertCount($expectedCount, $result);
    }

    #[Test]
    #[TestDox("Shall only return \$expectedCount time entries for the month \$requestedMonth and year \$requestedYear")]
    #[TestWith([
        "tenantId" => "REQUESTED_TENANT",
        "requestedMonth" => 12,
        "requestedYear" => 2025,
        "expectedCount" => 1,
        "entries" => [
            ["tenantId" => "REQUESTED_TENANT", "id" => "1", "month" => 12, "year" => 2025],
            ["tenantId" => "REQUESTED_TENANT", "id" => "2", "month" => 12, "year" => 2021],
            ["tenantId" => "REQUESTED_TENANT", "id" => "3", "month" => 12, "year" => 2022],
            ["tenantId" => "REQUESTED_TENANT", "id" => "4", "month" => 2, "year" => 2025],
            ["tenantId" => "REQUESTED_TENANT", "id" => "5", "month" => 1, "year" => 2025],
        ],
    ])]
    #[TestWith([
        "tenantId" => "REQUESTED_TENANT",
        "requestedMonth" => 12,
        "requestedYear" => 2025,
        "expectedCount" => 3,
        "entries" => [
            ["tenantId" => "REQUESTED_TENANT", "id" => "1", "month" => 12, "year" => 2025],
            ["tenantId" => "REQUESTED_TENANT", "id" => "2", "month" => 12, "year" => 2025],
            ["tenantId" => "REQUESTED_TENANT", "id" => "3", "month" => 12, "year" => 2025],
            ["tenantId" => "REQUESTED_TENANT", "id" => "4", "month" => 12, "year" => 2026],
            ["tenantId" => "REQUESTED_TENANT", "id" => "5", "month" => 1, "year" => 2025],
        ],
    ])]
    public function adksp(
        string $tenantId,
        int $requestedMonth,
        int $requestedYear,
        int $expectedCount,
        array $entries,
    ): void {

        $this->storageContext->expects($this->once())
            ->method("findAll")
            ->willReturn(array_map(static fn(array $it) => new TimeEntry($it), $entries));

        $result = $this->timeEntryService->getAllByMonth($requestedMonth, $requestedYear, $tenantId);

        $this->assertCount($expectedCount, $result);
    }

    #[Test]
    #[TestDox("Shall replace the given time entry if it exists")]
    #[DataProviderExternal(TimeEntryDataProvider::class, "validTimeEntryData")]
    public function ewioqpj(
        string $tenantId,
        string $id,
        int $year,
        int $month,
        int $dayOfMonth,
        int $minutes,
        int $hours,
    ): void {
        $entry = new TimeEntry(compact(
            "tenantId",
            "id",
            "year",
            "month",
            "dayOfMonth",
            "minutes",
            "hours",
        ));

        $this->storageContext->expects($this->once())
            ->method("replace")
            ->with($id, $entry);

        $this->storageContext->expects($this->never())
            ->method("save");

        $this->timeEntryService->save($entry, $tenantId);
    }

    #[Test]
    #[TestDox("Shall create and store the given time entry if it does not exist")]
    #[TestWith(["tenant-id"])]
    public function vmcxz(
        string $tenantId,
    ): void {
        $entry = new TimeEntry();

        $this->storageContext->expects($this->once())
            ->method("save")
            ->withAnyParameters();

        $this->storageContext->expects($this->never())
            ->method("replace");

        $this->timeEntryService->save($entry, $tenantId);
    }
}
