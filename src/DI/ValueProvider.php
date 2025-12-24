<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\DI;

use const EricFortmeyer\ActivityLog\DI\Tokens\ACTIVITY_LOG_APP_PASSWD_KEY;
use const EricFortmeyer\ActivityLog\DI\Tokens\ACTIVITY_LOG_CACHE_ENABLED;
use const EricFortmeyer\ActivityLog\DI\Tokens\ACTIVITY_LOG_FROM_ADDRESS;
use const EricFortmeyer\ActivityLog\DI\Tokens\ACTIVITY_STORE_DATABASE;
use const EricFortmeyer\ActivityLog\DI\Tokens\ACTIVITY_STORE_HOST;
use const EricFortmeyer\ActivityLog\DI\Tokens\ACTIVITY_STORE_USER;
use const EricFortmeyer\ActivityLog\DI\Tokens\APP_CONFIG_DB_FILENAME;
use const EricFortmeyer\ActivityLog\DI\Tokens\APP_CONFIG_TABLE_NAME;
use const EricFortmeyer\ActivityLog\DI\Tokens\DATA_DIR;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_APP_KEY;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_APP_PATH;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_LOGIN_PATH;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_SERVICE_TLS_HOST;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_SERVICE_TLS_PORT;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_TOKEN_TTL;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_USER;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_VALUE_TTL;
use const EricFortmeyer\ActivityLog\DI\Tokens\SSL_CERT;
use const EricFortmeyer\ActivityLog\DI\Tokens\SSL_KEY;

/**
 * @codeCoverageIgnore
 */
final class ValueProvider
{
    // phpcs:disable
    public string $appConfigTableName {
        get => $this->getVarFromEnv(APP_CONFIG_TABLE_NAME);
    }

    public string $appConfigDbFilename {
        get => \join(
            \DIRECTORY_SEPARATOR,
            [
                $this->getVarFromEnv(DATA_DIR),
                $this->getVarFromEnv(APP_CONFIG_DB_FILENAME),
            ]

        );
    }

    public string $appUser {
        get => $this->getVarFromEnv(ACTIVITY_STORE_USER);
    }

    public string $dbHost {
        get => $this->getVarFromEnv(ACTIVITY_STORE_HOST);
    }

    public string $dbName {
        get => $this->getVarFromEnv(ACTIVITY_STORE_DATABASE);
    }

    public string $dbPasswdStoreKey {
        get => $this->getVarFromEnv(ACTIVITY_LOG_APP_PASSWD_KEY);
    }

    public string $fromAddress {
        get => $this->getVarFromEnv(ACTIVITY_LOG_FROM_ADDRESS);
    }

    public string $secretsUser {
        get => $this->getVarFromEnv(SECRETS_USER);
    }

    public string $secretsTlsBaseUri {
        get => \sprintf(
            "%s:%s/v1/",
            $this->getVarFromEnv(SECRETS_SERVICE_TLS_HOST),
            $this->getVarFromEnv(SECRETS_SERVICE_TLS_PORT),
        );
    }

    public bool $secretsCacheEnabled {
        get => \in_array(
            $this->getVarFromEnv(ACTIVITY_LOG_CACHE_ENABLED),
            [1, "1", "On"]
        );
    }

    public string $secretsServiceAppPath {
        get => join(
            "/",
            [
                $this->getVarFromEnv(SECRETS_APP_PATH),
                $this->getVarFromEnv(SECRETS_APP_KEY),
            ]
        );
    }

    public string $secretsServiceLoginPath {
        get => $this->getVarFromEnv(SECRETS_LOGIN_PATH);
    }

    public int $secretsTokenTtl {
        get => $this->getNumericVarFromEnv(SECRETS_TOKEN_TTL);
    }

    public int $secretsValueTtl {
        get => $this->getNumericVarFromEnv(SECRETS_VALUE_TTL);
    }

    public string $sslKey {
        get => $this->getVarFromEnv(SSL_KEY);
    }

    public string $sslCert {
        get => $this->getVarFromEnv(SSL_CERT);
    }

    // phpcs:enable

    /**
     * @suppress PhanUnreferencedPrivateMethod
     */
    private function getNumericVarFromEnv(string $name): int
    {
        $var = \getenv($name);

        if (\is_numeric($var) === false) {
            throw new MissingEnvVariableException($name);
        }

        return (int) $var;
    }

    /**
     * @suppress PhanUnreferencedPrivateMethod
     */
    private function getVarFromEnv(string $name): string
    {
        $var = \getenv($name);

        if (\is_string($var) === false) {
            throw new MissingEnvVariableException($name);
        }

        return $var;
    }
}
