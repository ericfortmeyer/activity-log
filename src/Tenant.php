<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\AbstractModel;
use Phpolar\Model\EntityName;
use Phpolar\Model\PrimaryKey;
use Phpolar\Validators\MaxLength;
use Phpolar\Validators\Required;

#[EntityName("tenant")]
final class Tenant extends AbstractModel
{
    #[MaxLength(100)]
    #[Required]
    #[PrimaryKey]
    public string $id;
}
