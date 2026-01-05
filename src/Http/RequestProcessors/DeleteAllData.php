<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use EricFortmeyer\ActivityLog\Services\TemplateBinder;
use EricFortmeyer\ActivityLog\Services\TenantService;
use EricFortmeyer\ActivityLog\Tenant;
use EricFortmeyer\ActivityLog\UserInterface\Contexts\AccountDeleteSuccessContext;
use EricFortmeyer\ActivityLog\UserInterface\Contexts\BadRequestContext;
use EricFortmeyer\ActivityLog\Utils\Hasher;
use Phpolar\Model\Model;
use Phpolar\Phpolar\Auth\Authorize;

final class DeleteAllData extends AbstractTenantBasedRequestProcessor
{
    public function __construct(
        private readonly TenantService $tenantService,
        private readonly TemplateBinder $templateEngine,
        Hasher $hasher,
    ) {
        parent::__construct($hasher);
    }

    #[Authorize]
    public function process(
        #[Model] Tenant $tenant = new Tenant(),
    ): string {
        if ($tenant->id !== $this->getTenantId()) {
            return $this->templateEngine->apply(
                "400",
                new BadRequestContext(message: "Wrong account")
            );
        }

        $this->tenantService->purge($this->getTenantId());

        return $this->templateEngine->apply(
            "logout",
            new AccountDeleteSuccessContext(
                message: "Logging out..."
            ),
        );
    }
}
