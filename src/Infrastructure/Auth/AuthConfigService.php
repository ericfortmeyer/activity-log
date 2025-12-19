<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Infrastructure\Auth;

use EricFortmeyer\ActivityLog\Clients\SecretsClient;

class AuthConfigService
{
    public function __construct(
        private readonly SecretsClient $secretsClient,
    ) {}

    public function getClientSecret(): string
    {
        return $this->secretsClient->getValue("client-secret");
    }

    public function getCookieSecret(): string
    {
        return $this->secretsClient->getValue("cookie-secret");
    }

    public function getClientId(): string
    {
        return $this->secretsClient->getValue("client-id");
    }

    public function getDomain(): string
    {
        return $this->secretsClient->getValue("domain");
    }
}
