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
            ]
        ];
    }

    public static function invalidData(): array
    {
        return [
            "invalidEmail" => ["mailTo" => "not a valid email"],
        ];
    }
}
