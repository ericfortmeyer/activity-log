<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\AppConfig;
use Phpolar\Storage\StorageContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use SQLite3;
use SQLite3Result;
use SQLite3Stmt;

#[CoversClass(AppConfigService::class)]
final class AppConfigServiceTest extends TestCase
{
    #[Test]
    #[TestDox("Shall return false if there are no config values")]
    public function returnsFalse()
    {
        $storageStub = $this->createStub(StorageContext::class);
        $storageStub->method("findAll")
            ->willReturn([]);

        $sut = new AppConfigService(
            $storageStub,
            $this->createStub(SQLite3::class),
        );
        $this->assertFalse($sut->get());
    }

    #[Test]
    #[TestDox("Shall return the configuration if there are values")]
    #[TestWith([
        "config" => [
            [
                "id" => "1",
                "appName" => "myApp",
                "callbackPath" => "/callback",
                "loginPath" => "/login",
                "logoutPath" => "/logout"
            ]
        ],
        "expectedId" => "1",
        "expectedCallbackPath" => "/callback",
        "expectedLoginPath" => "/login",
        "expectedLogoutPath" => "/logout",
        "expectedAppName" => "myApp",
    ])]
    public function returnsConfig(
        array $config,
        string $expectedId,
        string $expectedCallbackPath,
        string $expectedLoginPath,
        string $expectedLogoutPath,
        string $expectedAppName,
    ) {
        $storageStub = $this->createStub(StorageContext::class);
        $storageStub->method("findAll")
            ->willReturn($config);

        $sut = new AppConfigService(
            $storageStub,
            $this->createStub(SQLite3::class),
        );
        $result = $sut->get();

        $this->assertInstanceOf(AppConfig::class, $result);

        assert($result !== false);

        $this->assertSame($expectedAppName, $result->appName);
        $this->assertSame($expectedId, $result->id);
        $this->assertSame($expectedCallbackPath, $result->callbackPath);
        $this->assertSame($expectedLoginPath, $result->loginPath);
        $this->assertSame($expectedLogoutPath, $result->logoutPath);
    }

    #[Test]
    #[TestDox("Shall return false when there are no config values")]
    #[TestWith(["0.10.5"])]
    public function updateVersionReturnsFalse(string $version)
    {
        $readStorageStub = $this->createStub(StorageContext::class);
        $readStorageStub->method("findAll")->willReturn([]);

        $sut = new AppConfigService(
            readStorage: $readStorageStub,
            writeConnection: $this->createStub(SQLite3::class),
        );

        $result = $sut->updateVersion($version);

        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox("Shall return false when config values")]
    #[TestWith([
        [
            "id" => "1",
            "appName" => "myApp",
            "callbackPath" => "/callback",
            "loginPath" => "/login",
            "logoutPath" => "/logout",
            "version" => "0.10.5",
        ],
        "INVALID_VERSION",
    ])]
    public function noUpdatesInvalidVersion(array $configWithInvalidVersion, string $version)
    {
        $readStorageStub = $this->createStub(StorageContext::class);
        $readStorageStub->method("findAll")->willReturn([$configWithInvalidVersion]);

        $sut = new AppConfigService(
            readStorage: $readStorageStub,
            writeConnection: $this->createStub(SQLite3::class),
        );

        $result = $sut->updateVersion($version);

        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox("Shall return false when config values")]
    #[TestWith([
        [
            "id" => "1",
            "appName" => "myApp",
            "callbackPath" => "/callback",
            "loginPath" => "/login",
            "logoutPath" => "/logout",
            "version" => "0.10.5",
        ],
        "1.10.5",
    ])]
    public function updatesVersion(array $config, string $version)
    {
        $stmtResultStub = $this->createStub(SQLite3Result::class);
        $sqliteStmtSpy = $this->createMock(SQLite3Stmt::class);
        $readStorageStub = $this->createStub(StorageContext::class);
        $readStorageStub->method("findAll")->willReturn([$config]);
        $writeConnectionSpy = $this->createMock(SQLite3::class);
        $writeConnectionSpy->expects($this->once())
            ->method("prepare")
            ->willReturn($sqliteStmtSpy);
        $sqliteStmtSpy->expects($this->exactly(2))
            ->method("bindValue")
            ->willReturn(true);
        $sqliteStmtSpy->expects($this->once())
            ->method("execute")
            ->willReturn($stmtResultStub);

        $sut = new AppConfigService(
            readStorage: $readStorageStub,
            writeConnection: $writeConnectionSpy,
        );

        $result = $sut->updateVersion($version);

        $this->assertTrue($result);
    }
}
