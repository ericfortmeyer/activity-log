<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\Tenant;
use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(TenantService::class)]
final class TenantServiceTest extends TestCase
{
    #[Test]
    #[TestDox("Should return false when prepare fails on existence check")]
    #[TestWith(["ANY_STRING"])]
    public function returnFalse(
        string $tenantId,
    ) {
        $dbSpy = $this->createMock(PDO::class);
        $dbSpy->expects($this->once())
            ->method("prepare")
            ->with(<<<SQL
            SELECT EXISTS (
                SELECT 1 FROM `tenant` WHERE `id`=:id LIMIT 1
            )
            SQL)
            ->willReturn(false);

        $sut = new TenantService($dbSpy);

        $result = $sut->exists($tenantId);

        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox("Should return the value bound to the column")]
    #[TestWith(["ANY_STRING", true, true])]
    #[TestWith(["ANY_STRING", false, false])]
    public function detectsExistance(
        string $tenantId,
        bool $exists,
        bool $expectedResult,
    ) {
        $stmtSpy = $this->createMock(PDOStatement::class);
        $dbSpy = $this->createMock(PDO::class);
        $dbSpy->expects($this->once())
            ->method("prepare")
            ->with(
                <<<SQL
            SELECT EXISTS (
                SELECT 1 FROM `tenant` WHERE `id`=:id LIMIT 1
            )
            SQL,
            )
            ->willReturn($stmtSpy);

        $stmtSpy->expects($this->once())
            ->method("bindColumn")
            ->with(1, false, PDO::PARAM_BOOL)
            ->willReturnCallback(
                static function (
                    int $column,
                    mixed &$var,
                    int $paramType
                ) use ($exists) {
                    $var = $exists;
                    return true;
                },
            );

        $stmtSpy->expects($this->once())
            ->method("execute")
            ->with(["id" => $tenantId])
            ->willReturn(true);

        $stmtSpy->expects($this->once())
            ->method("fetch");

        $sut = new TenantService($dbSpy);

        $result = $sut->exists($tenantId);

        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    #[TestDox("Should return false when prepare fails on fetching all")]
    public function returnFalseWhenFetching()
    {
        $dbSpy = $this->createMock(PDO::class);
        $dbSpy->expects($this->once())
            ->method("query")
            ->willReturn(false);

        $sut = new TenantService($dbSpy);

        $result = $sut->getAll();

        $this->assertSame([], $result);
    }

    #[Test]
    #[TestDox("Should return the fetch result")]
    public function getsAll()
    {
        $expectedResult = [new Tenant()];
        $stmtSpy = $this->createMock(PDOStatement::class);
        $dbSpy = $this->createMock(PDO::class);
        $dbSpy->expects($this->once())
            ->method("query")
            ->with(
                <<<SQL
                TABLE `tenant`;
                SQL,
                PDO::FETCH_CLASS
            )
            ->willReturn($stmtSpy);

        $stmtSpy->expects($this->once())
            ->method("fetchAll")
            ->willReturn($expectedResult);

        $sut = new TenantService($dbSpy);

        $result = $sut->getAll();

        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    #[TestDox("Should return false when saving and prepare fails")]
    public function returnFalseWhenSaving()
    {
        $dbSpy = $this->createMock(PDO::class);
        $dbSpy->expects($this->once())
            ->method("prepare")
            ->willReturn(false);

        $sut = new TenantService($dbSpy);

        $result = $sut->save(new Tenant());

        $this->assertNull($result);
    }

    #[Test]
    #[TestDox("Should return the value bound to the column")]
    #[TestWith(["TENANT_ID"])]
    public function saves(string $tenantId)
    {
        $tenant = new Tenant(["id" => $tenantId]);
        $stmtSpy = $this->createMock(PDOStatement::class);
        $dbSpy = $this->createMock(PDO::class);
        $dbSpy->expects($this->once())
            ->method("prepare")
            ->with(
                <<<SQL
            INSERT INTO `tenant`
            VALUES(:id)
            SQL,
            )
            ->willReturn($stmtSpy);

        $stmtSpy->expects($this->once())
            ->method("execute")
            ->with(["id" => $tenantId])
            ->willReturn(true);

        $sut = new TenantService($dbSpy);

        $sut->save($tenant);
    }

    #[Test]
    #[TestDox("Should return false when purging and prepare fails")]
    #[TestWith(["TENANT_ID"])]
    public function returnFalseWhenPurging(string $tenantId)
    {
        $dbSpy = $this->createMock(PDO::class);
        $dbSpy->expects($this->once())
            ->method("prepare")
            ->willReturn(false);

        $sut = new TenantService($dbSpy);

        $result = $sut->purge($tenantId);

        $this->assertNull($result);
    }

    #[Test]
    #[TestDox("Should return the value bound to the column")]
    #[TestWith(["TENANT_ID"])]
    public function purges(string $tenantId)
    {
        $stmtSpy = $this->createMock(PDOStatement::class);
        $dbSpy = $this->createMock(PDO::class);
        $dbSpy->expects($this->once())
            ->method("prepare")
            ->with(
                <<<SQL
            DELETE FROM `tenant`
            WHERE id=:id
            SQL,
            )
            ->willReturn($stmtSpy);

        $stmtSpy->expects($this->once())
            ->method("execute")
            ->with(["id" => $tenantId])
            ->willReturn(true);

        $sut = new TenantService($dbSpy);

        $sut->purge($tenantId);
    }
}
