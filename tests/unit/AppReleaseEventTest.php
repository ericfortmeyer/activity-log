<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\UnitTests\DataProviders\AppReleaseEventDataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

#[CoversClass(AppReleaseEvent::class)]
final class AppReleaseEventTest extends TestCase
{
    #[Test]
    #[TestDox("Shall know if it is valid")]
    #[DataProviderExternal(AppReleaseEventDataProvider::class, "validData")]
    public function validates(array | object $data): void
    {
        $sut = new AppReleaseEvent($data);

        $this->assertTrue($sut->isValid());
    }

    #[Test]
    #[TestDox("Shall know if it is not valid")]
    #[DataProviderExternal(AppReleaseEventDataProvider::class, "invalidData")]
    public function invalidates(array | object $data): void
    {
        $sut = new AppReleaseEvent($data);

        $this->assertFalse($sut->isValid());
    }

    #[Test]
    #[TestDox("Shall be creatable from a request")]
    #[TestWith(["{\"action\":\"published\",\"release\":{\"id\": 273131312, \"tag_name\":\"0.10.5\"}}", "588280332"])]
    public function creatable(string $requestBody, int|string $hookId): void
    {
        $request = $this->createStub(RequestInterface::class);
        $request->method("getHeader")
            ->with("X-GitHub-Hook-ID")
            ->willReturn([$hookId]);
        $sut = AppReleaseEvent::fromRequest($requestBody, $request);

        $this->assertTrue($sut->isValid());
    }

    #[Test]
    #[TestDox("Shall be creatable from an invalid request")]
    #[TestWith(["{\"release\": {\"id\": 273131312, \"tag_name\": \"0.10.5\"}}", "fffff"])]
    #[TestWith(["{\"release\": {\"id\": 273131312, \"tag_name\": \"0.10.5\"}}", "1000000000000000000"])]
    #[TestWith(["{\"release\": {\"id\": 273131312, \"tag_name\": \"0105\"}}", "100000000"])]
    #[TestWith(["{\"release\": {\"id\": \"dddddd273131312dddddd\", \"tag_name\": \"0.10.5\"}}", "100000000"])]
    #[TestWith(["{}", "100000000"])]
    #[TestWith(["", "100000000"])]
    #[TestWith(["", null])]
    public function createsInvalid(string $requestBody, int|string|null $hookId): void
    {
        $request = $this->createStub(RequestInterface::class);
        $request->method("getHeader")
            ->with("X-GitHub-Hook-ID")
            ->willReturn([$hookId]);
        $sut = AppReleaseEvent::fromRequest($requestBody, $request);

        $this->assertFalse($sut->isValid());
    }

    #[Test]
    #[TestDox("Shall know if request is a release event")]
    #[TestWith(["release", true])]
    #[TestWith(["push", false])]
    #[TestWith([null, false])]
    public function checksReleaseType(string | null $eventType, bool $expectedResult): void
    {
        $request = $this->createStub(RequestInterface::class);
        $request->method("getHeader")
            ->with("X-GitHub-Event")
            ->willReturn([$eventType]);
        $result = AppReleaseEvent::isReleaseEventRequest($request);

        $this->assertSame($expectedResult, $result);
    }
}
