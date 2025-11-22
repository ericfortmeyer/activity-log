<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmailConfig::class)]
final class EmailConfigTest extends TestCase
{
    #[Test]
    #[TestDox("Shall provide its headers")]
    #[TestWith([["key" => "value"]])]
    public function fdasjio(array $headers)
    {
        $sut = new EmailConfig($headers);

        $result = $sut->headers;

        $this->assertSame($result, $headers);
    }
}
