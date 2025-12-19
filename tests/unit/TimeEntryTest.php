<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use DateTimeImmutable;
use EricFortmeyer\ActivityLog\UnitTests\DataProviders\TimeEntryDataProvider;
use Phpolar\Phpolar\Auth\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeEntry::class)]
#[CoversClass(TenantData::class)]
final class TimeEntryTest extends TestCase
{
    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = new User(
            name: "FAKE_NAME",
            nickname: "FAKE_NICKNAME",
            email: "FAKE@FAKE.FAKE",
            avatarUrl: "https://FAKE.FAKE/FAKE.png",
        );
    }

    #[Test]
    #[DataProviderExternal(TimeEntryDataProvider::class, 'validTimeEntryData')]
    #[TestDox("Shall generate Id and Timestamp when created")]
    public function dopk(
        string $id,
        string $tenantId,
        int $dayOfMonth,
        int $year,
        int $month,
        int $hours,
        int $minutes,
    ): void {
        $entry = new TimeEntry(
            compact(
                "id",
                "tenantId",
                "dayOfMonth",
                "year",
                "month",
                "hours",
                "minutes",
            )
        );

        $entry->create($this->user->name);

        $this->assertNotEmpty($entry->id);
    }

    #[Test]
    #[DataProviderExternal(TimeEntryDataProvider::class, 'validTimeEntryData')]
    #[TestDox("Shall create a TimeEntry from an array of data")]
    public function adfskpo(
        string $id,
        string $tenantId,
        int $dayOfMonth,
        int $year,
        int $month,
        int $hours,
        int $minutes,
    ): void {
        $createdOn = new DateTimeImmutable("now");

        $entry = new TimeEntry(
            compact(
                "id",
                "tenantId",
                "dayOfMonth",
                "year",
                "month",
                "hours",
                "minutes",
                "createdOn"
            )
        );

        $this->assertInstanceOf(TimeEntry::class, $entry);
        $this->assertEquals($id, $entry->id);
        $this->assertEquals($dayOfMonth, $entry->dayOfMonth);
        $this->assertEquals($year, $entry->year);
        $this->assertEquals($hours, $entry->hours);
        $this->assertEquals($minutes, $entry->minutes);
    }

    #[Test]
    #[DataProviderExternal(TimeEntryDataProvider::class, 'validTimeEntryData')]
    #[TestDox(
        <<<TEXT
        Shall determine that the entry is valid: [
            id => \$id,
            tenantId => \$tenantId,
            dayOfMonth => \$dayOfMonth,
            month => \$month,
            year => \$year,
            hours => \$hours,
            minutes => \$minutes
           ]
        TEXT
    )]
    public function vmzcx(
        string $id,
        string $tenantId,
        int $dayOfMonth,
        int $year,
        int $month,
        int $hours,
        int $minutes,

    ): void {
        $createdOn = new DateTimeImmutable();
        $entry = new TimeEntry(
            compact(
                "id",
                "tenantId",
                "dayOfMonth",
                "year",
                "month",
                "hours",
                "minutes",
                "createdOn",
            )
        );

        $this->assertTrue($entry->isValid());
    }

    #[Test]
    #[DataProviderExternal(TimeEntryDataProvider::class, 'invalidTimeEntryData')]
    #[TestDox("Shall determine that the entry is invalid when given an invalid \$invalidProp: value => \$invalidValue")]
    public function qwkefop(
        string $invalidProp,
        string|int $invalidValue,
        string $id,
        string $tenantId,
        int $dayOfMonth,
        int $year,
        int $month,
        int $hours,
        int $minutes,
    ): void {
        $entry = new TimeEntry(
            compact(
                "id",
                "tenantId",
                "dayOfMonth",
                "year",
                "month",
                "hours",
                "minutes",
            )
        );

        $this->assertFalse($entry->isValid());
    }

    #[Test]
    #[TestWith(["01/01/2025", 1, 1, 2025])]
    #[TestWith(["11/01/2025", 11, 1, 2025])]
    #[TestWith(["01/11/2025", 1, 11, 2025])]
    #[TestDox("Shall provide the date in the expected format")]
    public function a(string $expectedDateFormatted, int $month, int $dayOfMonth, int $year)
    {
        $entry = new TimeEntry(compact("month", "year", "dayOfMonth"));
        $result = $entry->getDate();
        $this->assertSame($expectedDateFormatted, $result);
    }

    #[Test]
    #[TestWith(["5h 10m", 5, 10])]
    #[TestWith(["15h 1m", 15, 1])]
    #[TestDox("Shall provide the duration in the expected format")]
    public function testf(string $expectedDateFormatted, int $hours, int $minutes)
    {
        $entry = new TimeEntry(compact("minutes", "hours"));
        $result = $entry->getDuration();
        $this->assertSame($expectedDateFormatted, $result);
    }

    #[Test]
    #[TestWith(["dayOfMonth", 10, "2025/01/10"])]
    #[TestWith(["month", 1, "2025/01/10"])]
    #[TestWith(["year", 2025, "2025/01/10"])]
    #[TestWith(["hours", 0, "2025/01/10"])]
    #[TestWith(["minutes", 0, "2025/01/10"])]
    #[TestWith(["NOT A PROPERTY", null, "2025/01/10"])]
    #[TestDox("Shall provide the default value (\$expectedDefaultValue) for \$propName")]
    public function xxx(string $propName, mixed $expectedDefaultValue, string $dateString)
    {
        $date = new DateTimeImmutable($dateString);
        $result = TimeEntry::getDefaultValue($propName, $date);
        $this->assertSame($expectedDefaultValue, $result);
    }

    #[Test]
    #[TestWith(["tenant-id", "tenant-id", true])]
    #[TestWith(["tenant-id", "something else", false])]
    #[TestDox("Shall support filtering of instances by tenant id")]
    public function foadisj(
        string $tenantId,
        string $testTenantId,
        bool $expectedResult,
    ): void {
        $entry = new TimeEntry(compact("tenantId"));

        $filterFn = TimeEntry::forTenant($testTenantId);
        $result = $filterFn($entry);
        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    #[TestWith([10, 2025, 10, 2025, true])]
    #[TestWith([10, 2025, 10, 2026, false])]
    #[TestWith([10, 2025, 1, 2025, false])]
    #[TestDox("Shall support filtering of instances by month and year")]
    public function foadisjx(
        int $month,
        int $year,
        int $testMonth,
        int $testYear,
        bool $expectedResult,
    ): void {
        $entry = new TimeEntry(compact("month", "year"));

        $filterFn = TimeEntry::byMonthAndYear(month: $testMonth, year: $testYear);
        $result = $filterFn($entry);
        $this->assertSame($expectedResult, $result);
    }
}
