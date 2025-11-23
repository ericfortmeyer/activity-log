<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use EricFortmeyer\ActivityLog\Utils\Hasher;
use Phpolar\Phpolar\Auth\AbstractRestrictedAccessRequestProcessor;

abstract class AbstractTenantBasedRequestProcessor extends AbstractRestrictedAccessRequestProcessor
{
    public function __construct(
        private Hasher $hasher,
    ) {}

    public function getTenantId(): string
    {
        return $this->hasher->hash($this->user->nickname);
    }
}
