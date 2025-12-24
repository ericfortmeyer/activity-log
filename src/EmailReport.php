<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use DateTimeImmutable;
use Phpolar\Model\AbstractModel;
use Phpolar\Model\Hidden;
use Phpolar\Phpolar\Auth\User;
use Phpolar\Validators\{
    MaxLength,
    Pattern
};

final class EmailReport extends AbstractModel
{
    #[Pattern("/^.+@[[:alnum:]]+\.[[:alpha:]]{1,3}/")]
    #[MaxLength(100)]
    public string $mailTo;

    #[Hidden]
    public string $year;

    #[Hidden]
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
