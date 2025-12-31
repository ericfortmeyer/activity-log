<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UnitTests\DataProviders;

final class AppReleaseDataProvider
{
    public static function validData(): array
    {
        return [
            [
                [
                    "id" => 273131312,
                    "tag_name" => "0.10.5",
                ]
            ],
            [
                [
                    "id" => 273131312,
                    "tag_name" => "0.1.0",
                ]
            ],
            [
                (object) [
                    "id" => 273131312,
                    "tag_name" => "1.1.0-rc0",
                ]
            ],
            [
                [
                    "id" => 273131312,
                    "tag_name" => "1.1.0-alpha",
                ]
            ],
        ];
    }

    public static function invalidData(): array
    {
        return [
            [
                [
                    "id" => "ffffff",
                    "tag_name" => "0.10.5",
                ]
            ],
            [
                (object) [
                    "id" => 273131312,
                    "tag_name" => "010",
                ]
            ],
            [
                [
                    "id" => 273131312,
                    "tag_name" => "release",
                ]
            ],
            [
                null
            ],
        ];
    }
}
