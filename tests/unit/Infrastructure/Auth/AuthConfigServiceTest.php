<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UnitTests\Infrastructure\Auth;

use EricFortmeyer\ActivityLog\Clients\SecretsClient;
use EricFortmeyer\ActivityLog\Infrastructure\Auth\AuthConfigService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthConfigService::class)]
#[UsesClass(SecretsClient::class)]
final class AuthConfigServiceTest extends TestCase
{
    private AuthConfigService $sut;
    private SecretsClient&MockObject $secretsClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->secretsClient = $this->createMock(SecretsClient::class);
        $this->sut = new AuthConfigService(
            secretsClient: $this->secretsClient
        );
    }

    #[Test]
    #[TestDox("Shall retrieve the client secret")]
    #[TestWith(["client-secret"])]
    public function odji(string $clientSecret)
    {
        $this->secretsClient->expects($this->once())
            ->method("getValue")
            ->willReturn($clientSecret);
        $result = $this->sut->getClientSecret();

        $this->assertSame($clientSecret, $result);
    }

    #[Test]
    #[TestDox("Shall retrieve the client ID")]
    #[TestWith(["client-id"])]
    public function odja(string $clientId)
    {
        $this->secretsClient->expects($this->once())
            ->method("getValue")
            ->willReturn($clientId);
        $result = $this->sut->getClientId();

        $this->assertSame($clientId, $result);
    }

    #[Test]
    #[TestDox("Shall retrieve the cookie secret")]
    #[TestWith(["cookie-secret"])]
    public function odjiss(string $cookieSecret)
    {
        $this->secretsClient->expects($this->once())
            ->method("getValue")
            ->willReturn($cookieSecret);
        $result = $this->sut->getCookieSecret();

        $this->assertSame($cookieSecret, $result);
    }

    #[Test]
    #[TestDox("Shall retrieve the domain")]
    #[TestWith(["cookieSecret"])]
    public function odjwiss(string $cookieSecret)
    {
        $this->secretsClient->expects($this->once())
            ->method("getValue")
            ->willReturn($cookieSecret);
        $result = $this->sut->getDomain();

        $this->assertSame($cookieSecret, $result);
    }
}
