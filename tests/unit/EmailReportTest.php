<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\UnitTests\DataProviders\EmailReportDataProvider;
use Phpolar\Phpolar\Auth\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmailReport::class)]
final class EmailReportTest extends TestCase
{
    #[Test]
    #[TestDox("Shall know if it's properties have valid values: [\$mailTo]")]
    #[DataProviderExternal(EmailReportDataProvider::class, "validData")]
    public function validates(
        string $mailTo,
        int $month,
        string $year,
    ) {
        $emailReport = new EmailReport(
            compact(
                "mailTo",
                "month",
                "year",
            )
        );

        $this->assertTrue($emailReport->isValid());
    }

    #[Test]
    #[TestDox("Shall know if it's properties have invalid values: [\$mailTo]")]
    #[DataProviderExternal(EmailReportDataProvider::class, "invalidData")]
    public function invalidates(
        string $mailTo,
    ) {
        $emailReport = new EmailReport(
            compact(
                "mailTo",
            )
        );

        $this->assertFalse($emailReport->isValid());
    }

    #[Test]
    #[TestDox("Shall create subject based on it's properties [\$mailTo]")]
    #[TestWith(["fake@fake.com", 12, "2026", "Thelonius", "Thelonius's Report for December 2026"])]
    public function getsSubject(
        string $mailTo,
        int $month,
        string $year,
        string $name,
        string $expectedSubject,
    ) {
        $user = new User($name, "", "", "");
        $emailReport = new EmailReport(
            compact(
                "mailTo",
                "year",
                "month",
            )
        );

        $subject = $emailReport->getSubject($user);

        $this->assertSame($expectedSubject, $subject);
    }
}
