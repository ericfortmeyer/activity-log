<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\UnitTests\DataProviders\EmailReportDataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmailReport::class)]
final class EmailReportTest extends TestCase
{
    #[Test]
    #[TestDox("Shall know if it's properties have valid values: [\$mailTo, \$month, \$year]")]
    #[DataProviderExternal(EmailReportDataProvider::class, "validData")]
    public function asijofd(
        string $mailTo,
        int $month,
        int $year,
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
    #[TestDox("Shall know if it's properties have invalid values: [\$mailTo, \$month, \$year]")]
    #[DataProviderExternal(EmailReportDataProvider::class, "invalidData")]
    public function asijodasfd(
        string $mailTo,
        int $month,
        int $year,
    ) {
        $emailReport = new EmailReport(
            compact(
                "mailTo",
                "month",
                "year",
            )
        );

        $this->assertFalse($emailReport->isValid());
    }
}
