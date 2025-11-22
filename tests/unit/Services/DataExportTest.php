<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UnitTests\Services;

use EricFortmeyer\ActivityLog\Services\DataExportService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(DataExportService::class)]
final class DataExportTest extends TestCase
{
    #[Test]
    #[TestDox("Shall return the contents of the source file")]
    #[TestWith([
        "sourceFileName" => "tests/unit/files/test.csv",
        "expectedFileContent" => "\"TEST FILE\",\"TEST FILE\",\"TEST FILE\""
    ])]
    public function dfsjio(
        string $sourceFileName,
        string $expectedFileContent,
    ) {
        $result = new DataExportService(
            sourceFileName: $sourceFileName,
        )->export();

        $this->assertSame(
            $expectedFileContent,
            $result
        );
    }
}
