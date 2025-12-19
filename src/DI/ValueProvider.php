<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\DI;

use const EricFortmeyer\ActivityLog\DI\Tokens\ACTIVITY_LOG_APP_PASSWD_KEY;
use const EricFortmeyer\ActivityLog\DI\Tokens\ACTIVITY_LOG_CACHE_ENABLED;
use const EricFortmeyer\ActivityLog\DI\Tokens\ACTIVITY_STORE_DATABASE;
use const EricFortmeyer\ActivityLog\DI\Tokens\ACTIVITY_STORE_HOST;
use const EricFortmeyer\ActivityLog\DI\Tokens\ACTIVITY_STORE_USER;
use const EricFortmeyer\ActivityLog\DI\Tokens\APP_CONFIG_DB_FILENAME;
use const EricFortmeyer\ActivityLog\DI\Tokens\APP_CONFIG_TABLE_NAME;
use const EricFortmeyer\ActivityLog\DI\Tokens\DATA_DIR;
use const EricFortmeyer\ActivityLog\DI\Tokens\LOGIN_PASSWD_FILENAME;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_APP_KEY;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_APP_PATH;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_DIR;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_LOGIN_KEY;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_LOGIN_PATH;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_SERVICE_HOST;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_SERVICE_PORT;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_TOKEN_TTL;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_VALUE_TTL;

/**
 * @phan-file-suppress PhanUnreferencedUseConstant
 * @phan-file-suppress PhanReadOnlyPublicProperty
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

    public string $loginPasswdFilename {
        get => join(\DIRECTORY_SEPARATOR, [
            $this->secretsDir,
            $this->getVarFromEnv(LOGIN_PASSWD_FILENAME),
        ]);
    }

    /**
     * @suppress PhanUnreferencedPrivateProperty
     */
    private string $secretsDir {
        get => $this->getVarFromEnv(SECRETS_DIR);
    }

    public string $secretsBaseUri {
        get => \sprintf(
            "%s:%s/v1/",
            $this->getVarFromEnv(SECRETS_SERVICE_HOST),
            $this->getVarFromEnv(SECRETS_SERVICE_PORT),
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
        get =>
        join(
            "/",
            [
                $this->getVarFromEnv(SECRETS_LOGIN_PATH),
                $this->getVarFromEnv(SECRETS_LOGIN_KEY),
            ]
        );
    }

    public int $secretsTokenTtl {
        get => $this->getNumericVarFromEnv(SECRETS_TOKEN_TTL);
    }

    public int $secretsValueTtl {
        get => $this->getNumericVarFromEnv(SECRETS_VALUE_TTL);
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
