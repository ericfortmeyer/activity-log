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
    #[TestWith(["app-name"])]
    public function dijoa(string $appName)
    {
        $sut = new AppConfig(appName: $appName, callbackPath: "", loginPath: "", logoutPath: "");

        $this->assertSame($appName, $sut->appName);
    }

    #[Test]
    #[TestDox("Shall have an callbackPath property")]
    #[TestWith(["callback-path"])]
    public function dijdoa(string $callbackPath)
    {
        $sut = new AppConfig(appName: "", callbackPath: $callbackPath, loginPath: "", logoutPath: "");

        $this->assertSame($callbackPath, $sut->callbackPath);
    }

    #[Test]
    #[TestDox("Shall have an loginPath property")]
    #[TestWith(["login-path"])]
    public function dijxoa(string $loginPath)
    {
        $sut = new AppConfig(appName: "", callbackPath: "", loginPath: $loginPath, logoutPath: "");

        $this->assertSame($loginPath, $sut->loginPath);
    }

    #[Test]
    #[TestDox("Shall have an logoutPath property")]
    #[TestWith(["logout-path"])]
    public function dijgoa(string $logoutPath)
    {
        $sut = new AppConfig(appName: "", callbackPath: "", loginPath: "", logoutPath: $logoutPath);

        $this->assertSame($logoutPath, $sut->logoutPath);
    }
}
