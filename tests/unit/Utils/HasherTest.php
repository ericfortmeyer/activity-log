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
    public function hashes(
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

    #[Test]
    #[TestDox("Shall compute \$data as the \$expectedResult using \$signingKey")]
    #[TestWith([
        "mysigningkey",
        "{\"name\":\"Eric\"}",
        "73629a480ffe48fe01e97f2555f1973216ff70247e08a36aa8365ab8253aff18",
        true,
    ])]
    #[TestWith([
        "mysigningkey",
        "{\"name\":\"Eric\"}",
        "NON_MATCHING_SIGNATURE",
        false,
    ])]
    public function verifies(
        string $signingKey,
        string $data,
        string $signature,
        bool $expectedResult,
    ) {
        $hasher = new Hasher(
            hashingKey: "",
            signingKey: $signingKey,
        );

        $result = $hasher->verify($data, $signature);

        $this->assertSame($expectedResult, $result);
    }
}
