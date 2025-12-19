<?php

/**
 * @phan-file-suppress PhanReadOnlyPublicProperty
 * @phan-file-suppress PhanWriteOnlyPrivateProperty
 */

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\Clients\SecretsClient;

final class ActivityLogDbConfigService
{
    // phpcs:disable
    public string $appPassword {
        get {
            return $this->secretsClient->getValue($this->secretKey);
        }
    }

    public string $dsn {
        get {
            return sprintf(
                "mysql:dbname=%s;host=%s",
                $this->databaseName,
                $this->host,
            );
        }
    }
    // phpcs:enable

    public function __construct(
        public string $appUser,
        private string $host,
        private string $databaseName,
        private SecretsClient $secretsClient,
        private string $secretKey,
    ) {}
}
