<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UnitTests\Http\RequestProcessors;

use EricFortmeyer\ActivityLog\Http\RequestProcessors\AbstractTenantBasedRequestProcessor;
use Phpolar\Phpolar\Auth\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractTenantBasedRequestProcessor::class)]
final class AbstractTenantBasedRequestProcessorTest extends TestCase
{
    protected static User $user;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$user = new User(
            name: "",
            nickname: "FAKE_NICKNAME",
            email: "FAKE@FAKE.COM",
            avatarUrl: "https://FAKE.COM/images/FAKE.png"
        );
    }

    #[Test]
    #[TestDox("Shall compute (\$rawHashingKey) as the expected tenant id (\$computedTenantId)")]
    #[TestWith(["hashhashhashhash", "c9b1304638e067d3957a7921f3f75dec"])]
    #[TestWith(["hashhashhashhash", "c9b1304638e067d3957a7921f3f75dec"])]
    #[TestWith(["", "c4b3726644bc9313463e951cc83abf6a"])]
    public function ewoijf(string $rawHashingKey, string $computedTenantId)
    {
        $requestProcessor = new class ($rawHashingKey) extends AbstractTenantBasedRequestProcessor {
            public function process(): array|bool|int|null|object|string
            {
                throw new \Exception('Not implemented');
            }
        };
        $requestProcessor->user = self::$user;

        $result = $requestProcessor->getTenantId();

        $this->assertSame($computedTenantId, $result);
    }
}
