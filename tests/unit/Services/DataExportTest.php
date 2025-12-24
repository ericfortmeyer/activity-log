<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UnitTests\Services;

use EricFortmeyer\ActivityLog\Services\DataExportService;
use EricFortmeyer\ActivityLog\TimeEntry;
use Phpolar\Storage\StorageContext;
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
        [
            "id" => "aaaaaaaaaaaaaa",
            "tenantId" => "aaaaaaaaaaaaaa",
            "dayOfMonth" => 31,
            "year" => "2025",
            "month" => 12,
            "hours" => 24,
            "minutes" => 59,
            "createdOn" => "2020-10-10",
        ],
        "aaaaaaaaaaaaaa,aaaaaaaaaaaaaa,31,12,2025,24,59,2020-10-10T00:00:00+00:00\n",
    ])]
    public function dfsjio(
        array $data,
        string $expectedContent,
    ) {
        $entry = new TimeEntry($data);
        $timeEntryStorage = $this->createStub(StorageContext::class);
        $timeEntryStorage->method("findAll")
            ->willReturn([$entry]);
        $file = fopen("php://memory", "+w");

        $result = new DataExportService(
            storageContext: $timeEntryStorage,
            csv: $file,
        )->export();

        $this->assertNotFalse($result);

        $this->assertSame(
            $expectedContent,
            $result
        );
    }
}
