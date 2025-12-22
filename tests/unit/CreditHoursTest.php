<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\UnitTests\DataProviders\CreditHoursDataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreditHours::class)]
#[CoversClass(TenantData::class)]
final class CreditHoursTest extends TestCase
{
    #[Test]
    #[TestDox("Shall use the given tenant id")]
    #[DataProviderExternal(CreditHoursDataProvider::class, "validData")]
    public function dfijo(
        string $id,
        string $tenantId,
        int $month,
        int $year,
    ) {
        $sut = new CreditHours(
            compact(
                "id",
                "tenantId",
                "year",
                "month",
            )
        );

        $sut->create($tenantId);

        $this->assertSame($tenantId, $sut->tenantId);
    }

    #[Test]
    #[TestDox("Shall generate the expected id")]
    #[TestWith([
        "FAKE_TENANT_ID-2025-01",
        "FAKE_TENANT_ID",
        "2025",
        1
    ])]
    public function ijoadfs(
        string $expectedResult,
        string $tenantId,
        string $year,
        int $month,
    ) {
        $result = CreditHours::getIdFromMonth(
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
