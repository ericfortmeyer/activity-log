<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UnitTests\DataProviders;

final readonly class EmailReportDataProvider
{
    public static function validData(): array
    {
        return [
            [
                "mailTo" => "fake@fake.com",
                "month" =>  12,
                "year" => 2025
            ]
        ];
    }

    public static function invalidData(): array
    {
        return [
            "invalidEmail" => ["mailTo" => "not a valid email", "month" => 12, "year" => 2025],
            "invalidYear" => ["mailTo" => "fake@fake.com", "month" => 12, "year" => 0],
            "invalidMonth" => ["mailTo" => "fake@fake.com", "month" => 13, "year" => 2025],
        ];
    }
}
