<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UnitTests\DataProviders;

final readonly class RemarksForMonthDataProvider
{
    public static function validData(): array
    {
        return [
            [str_repeat("a", 20), str_repeat("a", 100), 12, 2025, str_repeat("a", 100)]
        ];
    }

    public static function invalidTimeEntryData(): array
    {
        return [
            "invalidId" => ["invalidProp" => "id", "invalidValue" => str_repeat("a", 21), "id" => str_repeat("a", 21), "tenantId" => "FAKE_TENANTID", "dayOfMonth" => 30, "year" => 2025, "month" => 1, "hours" => 8, "minutes" => 30],
            "invalidTenantId" => ["invalidProp" => "tenantId", "invalidValue" => str_repeat("a", 101), "id" => "FAKE_ID", "tenantId" => str_repeat("a", 101), "dayOfMonth" => 30, "year" => 2025, "month" => 1, "hours" => 8, "minutes" => 30],
            "invalidYear" => ["invalidProp" => "year", "invalidValue" => 2024, "id" => "FAKE_ID", "tenantId" => "FAKE_TENANTID", "dayOfMonth" => 1, "year" => 2024, "month" => 1, "hours" => 8, "minutes" => 30],
        ];
    }
}
