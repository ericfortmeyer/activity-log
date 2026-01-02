<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use DateTimeImmutable;
use Phpolar\Model\AbstractModel;
use Phpolar\Model\Hidden;
use Phpolar\Phpolar\Auth\User;
use Phpolar\Validators\{
    Max,
    MaxLength,
    Min,
    Pattern
};

final class EmailReport extends AbstractModel
{
    #[Pattern("/^.+@[[:alnum:]]+\.[[:alpha:]]{1,3}/")]
    #[MaxLength(100)]
    public string $mailTo;

    #[Min(2024)]
    #[Max(2030)]
    #[Hidden]
    public string $year;

    #[Hidden]
    #[Min(1)]
    #[Max(12)]
    public int $month;

    public function getSubject(User $user): string
    {
        $month = DateTimeImmutable::createFromFormat("!m", (string)$this->month);
        return sprintf(
            "%s's Report for %s %d",
            $user->name,
            $month ? $month->format("F") : "",
            $this->year
        );
    }
}
