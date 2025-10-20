<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeEntry::class)]
final class TimeEntryTest extends TestCase
{
    public function testCreateGeneratesIdAndTimestamp(): void
    {
        $entry = new TimeEntry();
        $entry->dayOfMonth = 1;
        $entry->year = 2025;
        $entry->hours = 8;
        $entry->minutes = 30;

        $entry->create();

        $this->assertNotEmpty($entry->id);
        $this->assertInstanceOf(DateTimeImmutable::class, $entry->createdOn);
    }

    public function testFromDataCreatesTimeEntryFromArray(): void
    {
        $data = [
            "id" => "test-id",
            "dayOfMonth" => 1,
            "year" => 2025,
            "hours" => 8,
            "minutes" => 30,
            "createdOn" => new DateTimeImmutable("2025-09-30 10:00:00"),
        ];

        $entry = TimeEntry::fromData($data);

        $this->assertInstanceOf(TimeEntry::class, $entry);
        $this->assertEquals("test-id", $entry->id);
        $this->assertEquals(1, $entry->dayOfMonth);
        $this->assertEquals(2025, $entry->year);
        $this->assertEquals(8, $entry->hours);
        $this->assertEquals(30, $entry->minutes);
        $this->assertEquals($data["createdOn"], $entry->createdOn);
    }

    public function testValidationPassesWithValidData(): void
    {
        $entry = new TimeEntry();
        $entry->id = "test-id";
        $entry->dayOfMonth = 1;
        $entry->year = 2025;
        $entry->hours = 8;
        $entry->minutes = 30;
        $entry->createdOn = new DateTimeImmutable();

        $this->assertTrue($entry->isValid());
    }

    public function testValidationFailsWithInvalidData(): void
    {
        $invalidCases = [
            "invalidDayOfMonth" => ["dayOfMonth" => 32, "year" => 2025, "hours" => 8, "minutes" => 30],
            "invalidYear" => ["dayOfMonth" => 1, "year" => 2024, "hours" => 8, "minutes" => 30],
            "invalidHours" => ["dayOfMonth" => 1, "year" => 2025, "hours" => 25, "minutes" => 30],
            "invalidMinutes" => ["dayOfMonth" => 1, "year" => 2025, "hours" => 8, "minutes" => 60],
        ];

        foreach ($invalidCases as $case => $data) {
            $entry = new TimeEntry();
            $entry->dayOfMonth = $data["dayOfMonth"];
            $entry->year = $data["year"];
            $entry->hours = $data["hours"];
            $entry->minutes = $data["minutes"];

            $this->assertFalse($entry->isValid(), "Validation should fail for $case");
        }
    }
}
