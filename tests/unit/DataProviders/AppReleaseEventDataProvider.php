<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UnitTests\DataProviders;

final class AppReleaseEventDataProvider
{
    public static function validData(): array
    {
        return [
            [
                [
                    "action" => "published",
                    "hookId" => "588280332",
                    "release" => ["id" => 12345678, "tag_name" => "0.10.5"]
                ],
            ],
            [
                (object) [
                    "action" => "published",
                    "hookId" => "588280332",
                    "release" => ["id" => 12345678, "tag_name" => "0.10.5"]
                ],
            ],
        ];
    }

    public static function invalidData(): array
    {
        return [
            [
                [
                    "action" => "published",
                    "release" => ["id" => 12345678, "tag_name" => "0.10.5"]
                ],
            ],
            [
                [
                    "action" => "published",
                ],
            ],
            [
                [
                    "hookId" => "588280332",
                    "action" => "created",
                    "release" => ["id" => 12345678, "tag_name" => "0.10.5"],
                ],
            ],
            [
                [
                    "hookId" => "fff2",
                    "action" => "published",
                    "release" => ["id" => 12345678, "tag_name" => "0.10.5"],
                ],
            ],
            [
                (object) [
                    "action" => "published",
                    "release" => ["id" => 12345678, "tag_name" => "0.10.5"],
                ],
            ],
            [
                [],
            ],
        ];
    }
}
