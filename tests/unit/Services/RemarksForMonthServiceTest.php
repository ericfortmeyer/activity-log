<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\CreditHours;
use EricFortmeyer\ActivityLog\RemarksForMonth;
use EricFortmeyer\ActivityLog\UnitTests\DataProviders\RemarksForMonthDataProvider;
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

#[CoversClass(RemarksForMonthService::class)]
#[UsesClass(RemarksForMonth::class)]
final class RemarksForMonthServiceTest extends TestCase
{
    private StorageContext&MockObject $storageContext;
    private RemarksForMonthService $remarksForMonthService;

    protected function setUp(): void
    {
        $this->storageContext = $this->createMock(StorageContext::class);
        $this->remarksForMonthService = new RemarksForMonthService($this->storageContext);
    }

    #[Test]
    #[TestDox("Shall return the requested credit hours when found")]
    #[TestWith(["test-id"])]
    public function fqwjopei(string $id): void
    {
        $expectedEntry = new RemarksForMonth(["id" => $id]);

        $this->storageContext->expects($this->once())
            ->method("find")
            ->with($id)
            ->willReturn(new \Phpolar\Storage\Result($expectedEntry));

        $result = $this->remarksForMonthService->get($id);

        $this->assertInstanceOf(RemarksForMonth::class, $result);
        $this->assertSame($id, $result->id);
    }

    #[Test]
    #[TestDox("Shall return a not found result when the credit hours is not found")]
    #[TestWith(["non-existent-id"])]
    public function testGetReturnsNotFoundWhenEntryDoesNotExist(string $entryId): void
    {
        $this->storageContext->expects($this->once())
            ->method("find")
            ->with($entryId)
            ->willReturn(new \Phpolar\Storage\Result(new NotFound()));

        $result = $this->remarksForMonthService->get($entryId);

        $this->assertInstanceOf(NotFound::class, $result);
    }

    #[Test]
    #[TestDox("Shall replace the given time entry if it exists")]
    #[DataProviderExternal(RemarksForMonthDataProvider::class, "validData")]
    public function ewioqpj(
        string $tenantId,
        string $id,
        int $year,
        int $month,
        string $remarks
    ): void {
        $entry = new RemarksForMonth(compact(
            "tenantId",
            "id",
            "year",
            "month",
            "remarks"
        ));

        $this->storageContext->expects($this->once())
            ->method("replace")
            ->with($id, $entry);

        $this->storageContext->expects($this->never())
            ->method("save");

        $this->remarksForMonthService->save($entry, $tenantId);
    }

    #[Test]
    #[TestDox("Shall create and store the given remarks if it does not exist")]
    #[TestWith(["tenant-id"])]
    public function vmcxz(
        string $tenantId,
    ): void {
        $remarks = new RemarksForMonth(["year" => 2025, "month" => 12]);

        $this->storageContext->expects($this->once())
            ->method("save")
            ->withAnyParameters();

        $this->storageContext->expects($this->never())
            ->method("replace");

        $this->remarksForMonthService->save($remarks, $tenantId);
    }
}
