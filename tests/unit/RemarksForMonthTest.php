<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\UnitTests\DataProviders\RemarksForMonthDataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemarksForMonth::class)]
final class RemarksForMonthTest extends TestCase
{
    #[Test]
    #[TestDox("Shall use the given tenant id")]
    #[DataProviderExternal(RemarksForMonthDataProvider::class, "validData")]
    public function dfijo(
        string $id,
        string $tenantId,
        int $month,
        int $year,
        string $remarks,
    ) {
        $sut = new RemarksForMonth(
            compact(
                "id",
                "year",
                "month",
            )
        );

        $sut->create($tenantId);

        $this->assertSame($tenantId, $sut->tenantId);
    }

    #[Test]
    #[TestDox("Shall use the given data to create an instance of itself")]
    #[DataProviderExternal(RemarksForMonthDataProvider::class, "validData")]
    public function jasdfpoi(
        string $id,
        string $tenantId,
        int $month,
        int $year,
        string $remarks,
    ) {
        $sut = RemarksForMonth::fromData(
            compact(
                "id",
                "tenantId",
                "year",
                "month",
                "remarks",
            )
        );

        $this->assertSame($tenantId, $sut->tenantId);
        $this->assertSame($id, $sut->id);
        $this->assertSame($month, $sut->month);
        $this->assertSame($year, $sut->year);
        $this->assertSame($remarks, $sut->remarks);
    }

    #[Test]
    #[TestDox("Shall generate the expected id")]
    #[TestWith([
        "FAKE_TENANT_ID-2025-01",
        "FAKE_TENANT_ID",
        2025,
        1
    ])]
    public function ijoadfs(
        string $expectedResult,
        string $tenantId,
        int $year,
        int $month,
    ) {
        $result = RemarksForMonth::getIdFromMonth(
            year: $year,
            month: $month,
            tenantId: $tenantId
        );

        $this->assertSame(
            $expectedResult,
            $result
        );
    }
}
