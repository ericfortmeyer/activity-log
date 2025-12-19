<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UnitTests\DataProviders;

final readonly class TimeEntryDataProvider
{
    public static function setForTotals(): array
    {
        return [
            [
                [
                    [
                        "id" => str_repeat("a", 20),
                        "tenantId" => str_repeat("a", 100),
                        "dayOfMonth" => 31,
                        "year" => 2025,
                        "month" => 12,
                        "hours" => 5,
                        "minutes" => 0
                    ],
                    [
                        "id" => str_repeat("a", 20),
                        "tenantId" => str_repeat("a", 100),
                        "dayOfMonth" => 31,
                        "year" => 2025,
                        "month" => 12,
                        "hours" => 5,
                        "minutes" => 0
                    ],
                    [
                        "id" => str_repeat("a", 20),
                        "tenantId" => str_repeat("a", 100),
                        "dayOfMonth" => 31,
                        "year" => 2025,
                        "month" => 12,
                        "hours" => 5,
                        "minutes" => 0,
                    ],

                ],
                15
            ],
            [
                [
                    [
                        "id" => str_repeat("a", 20),
                        "tenantId" => str_repeat("a", 100),
                        "dayOfMonth" => 31,
                        "year" => 2025,
                        "month" => 12,
                        "hours" => 5,
                        "minutes" => 0
                    ],
                    [
                        "id" => str_repeat("a", 20),
                        "tenantId" => str_repeat("a", 100),
                        "dayOfMonth" => 31,
                        "year" => 2025,
                        "month" => 12,
                        "hours" => 5,
                        "minutes" => 30
                    ],
                    [
                        "id" => str_repeat("a", 20),
                        "tenantId" => str_repeat("a", 100),
                        "dayOfMonth" => 31,
                        "year" => 2025,
                        "month" => 12,
                        "hours" => 5,
                        "minutes" => 30,
                    ],

                ],
                16
            ],
        ];
    }

    public static function validTimeEntryData(): array
    {
        return [
            [
                "id" => str_repeat("a", 20),
                "tenantId" => str_repeat("a", 100),
                "dayOfMonth" => 31,
                "year" => 2025,
                "month" => 12,
                "hours" => 24,
                "minutes" => 59
            ]
        ];
    }

    public static function invalidTimeEntryData(): array
    {
        return [
            "invalidId" => ["invalidProp" => "id", "invalidValue" => str_repeat("a", 21), "id" => str_repeat("a", 21), "tenantId" => "FAKE_TENANTID", "dayOfMonth" => 30, "year" => 2025, "month" => 1, "hours" => 8, "minutes" => 30],
            "invalidTenantId" => ["invalidProp" => "tenantId", "invalidValue" => str_repeat("a", 101), "id" => "FAKE_ID", "tenantId" => str_repeat("a", 101), "dayOfMonth" => 30, "year" => 2025, "month" => 1, "hours" => 8, "minutes" => 30],
            "invalidDayOfMonth" => ["invalidProp" => "dayOfMonth", "invalidValue" => 32, "id" => "FAKE_ID", "tenantId" => "FAKE_TENANTID", "dayOfMonth" => 32, "year" => 2025, "month" => 1, "hours" => 8, "minutes" => 30],
            "invalidYear" => ["invalidProp" => "year", "invalidValue" => 2024, "id" => "FAKE_ID", "tenantId" => "FAKE_TENANTID", "dayOfMonth" => 1, "year" => 2024, "month" => 1, "hours" => 8, "minutes" => 30],
            "invalidHours" => ["invalidProp" => "hours", "invalidValue" => 25, "id" => "FAKE_ID", "tenantId" => "FAKE_TENANTID", "dayOfMonth" => 1, "year" => 2025, "month" => 1, "hours" => 25, "minutes" => 30],
            "invalidMinutes" => ["invalidProp" => "minutes", "invalidValue" => 60, "id" => "FAKE_ID", "tenantId" => "FAKE_TENANTID", "dayOfMonth" => 1, "year" => 2025, "month" => 1, "hours" => 8, "minutes" => 60],
        ];
    }
}
