<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\AbstractModel;
use Phpolar\Validators\{
    MaxLength,
    Pattern
};

/**
 * @phan-file-suppress PhanReadOnlyPublicProperty
 */
final class EmailReport extends AbstractModel
{
    #[Pattern("/^.+@[[:alnum:]]+\.[[:alpha:]]{1,3}/")]
    #[MaxLength(100)]
    public string $mailTo;
}
