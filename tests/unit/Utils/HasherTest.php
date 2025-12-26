<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Utils;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(Hasher::class)]
final class HasherTest extends TestCase
{
    #[Test]
    #[TestDox("Shall compute \$input as the \$expectedOutput using \$hashingKey")]
    #[TestWith(["hashhashhashhash", "FAKE_NICKNAME", "c9b1304638e067d3957a7921f3f75dec"])]
    #[TestWith(["", "FAKE_NICKNAME", "c4b3726644bc9313463e951cc83abf6a"])]
    public function dfsjio(
        string $hashingKey,
        string $input,
        string $expectedOutput,
    ) {
        $hasher = new Hasher(
            hashingKey: $hashingKey,
            signingKey: $hashingKey,
        );

        $result = $hasher->hash($input);

        $this->assertSame($expectedOutput, $result);
    }
}
