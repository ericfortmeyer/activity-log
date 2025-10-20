<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\AbstractModel;
use Phpolar\Validators\Max;
use Phpolar\Validators\MaxLength;
use Phpolar\Validators\Min;
use Phpolar\Validators\Pattern;

final class EmailReportContext extends AbstractModel
{
    #[Pattern("/^.+@[[:alnum:]]+\.[[:alpha:]]{3}/")]
    #[MaxLength(100)]
    public string $mailTo;

    #[Min(1900)]
    #[Max(2100)]
    public int $year;

    #[Min(1)]
    #[Max(12)]
    public int $month;
}
