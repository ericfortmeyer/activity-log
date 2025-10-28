<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Storage\NotFound;
use Phpolar\Storage\StorageContext;
use PHPUnit\Framework\Attributes\CoversClass;
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

    public function testDeleteReturnsTimeEntryWhenFound(): void
    {
        $entryId = "test-id";
        $expectedEntry = new TimeEntry();

        $this->storageContext->expects($this->once())
            ->method("remove")
            ->with($entryId)
            ->willReturn(new \Phpolar\Storage\Result($expectedEntry));

        $result = $this->timeEntryService->delete($entryId);

        $this->assertInstanceOf(TimeEntry::class, $result);
    }

    public function testDeleteReturnsNotFoundWhenEntryDoesNotExist(): void
    {
        $entryId = "non-existent-id";

        $this->storageContext->expects($this->once())
            ->method("remove")
            ->with($entryId)
            ->willReturn(new \Phpolar\Storage\Result(new NotFound()));

        $result = $this->timeEntryService->delete($entryId);

        $this->assertInstanceOf(NotFound::class, $result);
    }

    public function testDeleteHandlesStorageError(): void
    {
        $entryId = "test-id";
        $error = new \RuntimeException("Storage error");

        $this->storageContext->expects($this->once())
            ->method("remove")
            ->with($entryId)
            ->willThrowException($error);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Storage error");

        $this->timeEntryService->delete($entryId);
    }

    public function testGetHandlesStorageError(): void
    {
        $entryId = "test-id";
        $error = new \RuntimeException("Storage error");

        $this->storageContext->expects($this->once())
            ->method("find")
            ->with($entryId)
            ->willThrowException($error);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Storage error");

        $this->timeEntryService->get($entryId);
    }

    public function testGetAllHandlesStorageError(): void
    {
        $error = new \RuntimeException("Storage error");

        $this->storageContext->expects($this->once())
            ->method("findAll")
            ->willThrowException($error);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Storage error");

        $this->timeEntryService->getAll();
    }

    public function testGetReturnsTimeEntryWhenFound(): void
    {
        $entryId = "test-id";
        $expectedEntry = new TimeEntry();

        $this->storageContext->expects($this->once())
            ->method("find")
            ->with($entryId)
            ->willReturn(new \Phpolar\Storage\Result($expectedEntry));

        $result = $this->timeEntryService->get($entryId);

        $this->assertInstanceOf(TimeEntry::class, $result);
    }

    public function testGetReturnsNotFoundWhenEntryDoesNotExist(): void
    {
        $entryId = "non-existent-id";

        $this->storageContext->expects($this->once())
            ->method("find")
            ->with($entryId)
            ->willReturn(new \Phpolar\Storage\Result(new NotFound()));

        $result = $this->timeEntryService->get($entryId);

        $this->assertInstanceOf(NotFound::class, $result);
    }

    public function testGetAllReturnsArrayOfTimeEntries(): void
    {
        $entries = [
            ["id" => "1", "description" => "Test 1"],
            ["id" => "2", "description" => "Test 2"],
        ];

        $this->storageContext->expects($this->once())
            ->method("findAll")
            ->willReturn($entries);

        $result = $this->timeEntryService->getAll();

        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(TimeEntry::class, $result);
    }

    public function testSaveStoresTimeEntry(): void
    {
        $entry = new TimeEntry();
        $entry->id = "test-id";
        $entry->dayOfMonth = 1;
        $entry->year = 2025;

        $this->storageContext->expects($this->once())
            ->method("save")
            ->with($entry->id, $entry);

        $this->timeEntryService->save($entry);
    }
}
