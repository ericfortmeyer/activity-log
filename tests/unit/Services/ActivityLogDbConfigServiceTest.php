<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\Clients\SecretsClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(ActivityLogDbConfigService::class)]
final class ActivityLogDbConfigServiceTest extends TestCase
{
    #[Test]
    #[TestDox("Shall retrieve the app password from the secrets client")]
    #[TestWith([
        "activity_log_app",
        "activity-log.phpolar.org",
        "activity_log",
        "activity_log_db_passwd",
        "A_SECRET_VALUE",
    ])]
    public function getsAppPassword(
        string $appUser,
        string $host,
        string $dbName,
        string $secretKey,
        string $expectedSecretPasswd,
    ) {
        $secretsClientSpy = $this->createMock(SecretsClient::class);
        $secretsClientSpy->expects($this->once())
            ->method("getValue")
            ->with($secretKey)
            ->willReturn($expectedSecretPasswd);

        $sut = new ActivityLogDbConfigService(
            appUser: $appUser,
            host: $host,
            databaseName: $dbName,
            secretKey: $secretKey,
            secretsClient: $secretsClientSpy,
        );

        $this->assertSame($expectedSecretPasswd, $sut->appPassword);
    }

    #[Test]
    #[TestDox("Shall retrieve the app password from the secrets client")]
    #[TestWith([
        "activity_log_app",
        "activity-log.phpolar.org",
        "activity_log",
        "activity_log_db_passwd",
        "mysql:dbname=activity_log;host=activity-log.phpolar.org",
    ])]
    public function getsDSN(
        string $appUser,
        string $host,
        string $dbName,
        string $secretKey,
        string $expectedDsn,
    ) {
        $secretsClientSpy = $this->createStub(SecretsClient::class);
        $sut = new ActivityLogDbConfigService(
            appUser: $appUser,
            host: $host,
            databaseName: $dbName,
            secretKey: $secretKey,
            secretsClient: $secretsClientSpy,
        );

        $this->assertSame($expectedDsn, $sut->dsn);
    }
}
