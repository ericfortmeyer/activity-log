<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\{
    Model\AbstractModel,
    Model\Hidden,
    Validators\MaxLength,
    Validators\Required
};
use Phpolar\Phpolar\Auth\User;

abstract class TenantData extends AbstractModel
{
    #[MaxLength(100)]
    #[Required]
    #[Hidden]
    public string $tenantId;

    protected function initForTenant(User $user): void
    {
        $this->tenantId = $user->nickname;
    }
}
