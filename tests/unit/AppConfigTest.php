<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(AppConfig::class)]
final class AppConfigTest extends TestCase
{
    #[Test]
    #[TestDox("Shall have an appName property")]
    #[TestWith(
        [
            ["appName" => "app-name", "callbackPath" => "", "loginPath" => "", "logoutPath" => ""],
            "app-name"
        ]
    )]
    public function dijoa(array $data, string $appName)
    {
        $sut = new AppConfig($data);

        $this->assertSame($appName, $sut->appName);
    }

    #[Test]
    #[TestDox("Shall have an callbackPath property")]
    #[TestWith(
        [
            ["appName" => "app-name", "callbackPath" => "callback-path", "loginPath" => "", "logoutPath" => ""],
            "callback-path"
        ]
    )]
    public function dijdoa(array $data, string $callbackPath)
    {
        $sut = new AppConfig($data);

        $this->assertSame($callbackPath, $sut->callbackPath);
    }

    #[Test]
    #[TestDox("Shall have an loginPath property")]
    #[TestWith(
        [
            [
                "appName" => "app-name",
                "callbackPath" => "callback-path",
                "loginPath" => "login-path",
                "logoutPath" => ""
            ],
            "login-path"
        ]
    )]
    public function dijxoa(array $data, string $loginPath)
    {
        $sut = new AppConfig($data);

        $this->assertSame($loginPath, $sut->loginPath);
    }

    #[Test]
    #[TestDox("Shall have an logoutPath property")]
    #[TestWith([
        [
            "appName" => "app-name",
            "callbackPath" => "callback-path",
            "loginPath" => "login-path",
            "logoutPath" => "logout-path"
        ],
        "logout-path"
    ])]
    public function dijgoa(array $data, string $logoutPath)
    {
        $sut = new AppConfig($data);

        $this->assertSame($logoutPath, $sut->logoutPath);
    }
}
