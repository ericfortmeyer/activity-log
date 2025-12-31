<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\UnitTests\DataProviders\AppReleaseDataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(AppRelease::class)]
final class AppReleaseTest extends TestCase
{
    #[Test]
    #[TestDox("Shall know if it is valid")]
    #[DataProviderExternal(AppReleaseDataProvider::class, "validData")]
    public function validates(array | object $data)
    {
        $sut = new AppRelease($data);

        $this->assertTrue($sut->isValid());
    }

    #[Test]
    #[TestDox("Shall know if it is not valid")]
    #[DataProviderExternal(AppReleaseDataProvider::class, "invalidData")]
    public function invalidates(array | object | null $data)
    {
        $sut = new AppRelease($data);

        $this->assertFalse($sut->isValid());
    }
}
