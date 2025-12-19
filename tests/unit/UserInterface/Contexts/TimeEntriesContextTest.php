<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UserInterface\Contexts;

use EricFortmeyer\ActivityLog\CreditHours;
use EricFortmeyer\ActivityLog\MonthFilters;
use EricFortmeyer\ActivityLog\RemarksForMonth;
use EricFortmeyer\ActivityLog\TimeEntry;
use EricFortmeyer\ActivityLog\UnitTests\DataProviders\TimeEntryDataProvider;
use Phpolar\Phpolar\Auth\User;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class TimeEntriesContextTest extends TestCase
{
    #[Test]
    #[TestDox("Shall know if the report cannot be shown")]
    public function cannotshowreport()
    {
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: []
        );

        $this->assertFalse($sut->canShowReport());
    }

    #[Test]
    #[TestDox("Shall know if the report can be shown")]
    public function canshow()
    {
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [new TimeEntry()]
        );

        $this->assertTrue($sut->canShowReport());
    }
    #[Test]
    #[TestDox("Shall know if credit hours cannot be shown")]
    #[TestWith([["hours" => 0]])]
    public function cannotshowcredithours(array $creditHoursData)
    {
        $creditHours = new CreditHours($creditHoursData);
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [],
            creditHours: $creditHours,
        );

        $this->assertFalse($sut->shouldShowCreditHours());
    }

    #[Test]
    #[TestDox("Shall know if credit hours can be shown")]
    #[TestWith([["hours" => 10]])]
    public function canshowcredithours(array $creditHoursData)
    {
        $creditHours = new CreditHours($creditHoursData);
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [new TimeEntry()],
            creditHours: $creditHours,
        );

        $this->assertTrue($sut->shouldShowCreditHours());
    }

    #[Test]
    #[TestDox("Shall return credit hours")]
    #[TestWith([["hours" => 10], 10])]
    public function cangetcredithours(array $creditHoursData, int $hours)
    {
        $creditHours = new CreditHours($creditHoursData);
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [new TimeEntry()],
            creditHours: $creditHours,
        );

        $this->assertSame($sut->getCreditHours(), $hours);
    }

    #[Test]
    #[TestDox("Shall return total hours")]
    #[DataProviderExternal(TimeEntryDataProvider::class, "setForTotals")]
    public function cangettotalhours(array $timeEntriesData, int $expectedTotalHours)
    {
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: array_map(
                static fn(array $timeEntryData) => new TimeEntry($timeEntryData),
                $timeEntriesData,
            ),
        );

        $this->assertSame($expectedTotalHours, $sut->getTotalHours());
    }

    #[Test]
    #[TestDox("Shall return add activity instructions")]
    public function cangetinstructions()
    {
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [],
        );

        $this->assertSame(
            "Select a date. Then enter the hours and minutes "
                . "for the duration of the activity.",
            $sut->getAddActivityInstructions()
        );
    }

    #[Test]
    #[TestDox("Shall return add activity form title")]
    public function cangetformtitle()
    {
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [],
        );

        $this->assertSame(
            "Add an activity",
            $sut->getAddActivityFormTitle()
        );
    }

    #[Test]
    #[TestDox("Shall get remarks id")]
    #[TestWith([["id" => "1"], "1"])]
    public function cangethasremarksid(array $remarksData, string $expectedRemarksId)
    {
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [],
            remarks: new RemarksForMonth($remarksData),
        );

        $this->assertSame($expectedRemarksId, $sut->getRemarksId());
    }

    #[Test]
    #[TestDox("Shall know if it has remarks")]
    #[TestWith([["remarks" => "1"]])]
    public function cangethasremarks(array $remarksData)
    {
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [],
            remarks: new RemarksForMonth($remarksData),
        );

        $this->assertTrue($sut->hasRemarks());
    }

    #[Test]
    #[TestDox("Shall know if it does not have remarks")]
    #[TestWith([["remarks" => ""]])]
    public function cangetdonthaveremarks(array $remarksData)
    {
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [],
            remarks: new RemarksForMonth($remarksData),
        );

        $this->assertFalse($sut->hasRemarks());
    }

    #[Test]
    #[TestDox("Shall get month filter")]
    #[TestWith(["filters" => ["filterMonth" => 1], "expectedMonthFilter" => 1])]
    public function cangetmonthfilter(array $filters, int $expectedMonthFilter)
    {
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [],
            filters: new MonthFilters($filters),
        );

        $this->assertSame($expectedMonthFilter, $sut->getMonthFilter());
    }

    #[Test]
    #[TestDox("Shall get month filter default")]
    #[TestWith(["expectedMonthFilterDefault" => 0])]
    public function cangetmonthfilterdefault(int $expectedMonthFilterDefault)
    {
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [],
        );

        $this->assertSame($expectedMonthFilterDefault, $sut->getMonthFilter());
    }

    #[Test]
    #[TestDox("Shall get year filter")]
    #[TestWith(["filters" => ["filterYear" => 1], "expectedYearFilter" => 1])]
    public function cangetyearfilter(array $filters, int $expectedYearFilter)
    {
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [],
            filters: new MonthFilters($filters),
        );

        $this->assertSame($expectedYearFilter, $sut->getYearFilter());
    }

    #[Test]
    #[TestDox("Shall get year filter default")]
    #[TestWith(["expectedYearFilterDefault" => 0])]
    public function cangetyearfilterdefault(int $expectedYearFilterDefault)
    {
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [],
        );

        $this->assertSame($expectedYearFilterDefault, $sut->getYearFilter());
    }

    #[Test]
    #[TestDox("Shall get month query")]
    #[TestWith([
        "filters" => ["filterMonth" => 3, "filterYear" => 2020],
        "expectedMonthQuery" => "filterMonth=3&filterYear=2020"
    ])]
    public function cangetfilterQuery(array $filters, string $expectedMonthQuery)
    {
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [],
            filters: new MonthFilters($filters),
        );

        $this->assertSame($expectedMonthQuery, $sut->getMonthQuery());
    }

    #[Test]
    #[TestDox("Shall get delete url with month query")]
    #[TestWith([
        "filters" => ["filterMonth" => 3, "filterYear" => 2020],
        "deleteUrl" => "/test-url",
        "timeEntryData" => ["id" => "test-id"],
        "expectedUrlWithMonthQuery" => "/test-url/test-id?filterMonth=3&filterYear=2020"
    ])]
    public function cangetdeleteurlandfilterQuery(
        array $filters,
        string $deleteUrl,
        array $timeEntryData,
        string $expectedUrlWithMonthQuery
    ) {
        $timeEntry = new TimeEntry($timeEntryData);
        $sut = new TimeEntriesContext(
            user: new User("", "", "", ""),
            timeEntries: [],
            filters: new MonthFilters($filters),
            deleteUrl: $deleteUrl,
        );

        $this->assertSame($expectedUrlWithMonthQuery, $sut->getEntryDeleteUrl($timeEntry));
    }
}
