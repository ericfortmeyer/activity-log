<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Clients;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;

use const EricFortmeyer\ActivityLog\config\FileNames\LOGIN_PASSWD;
use const EricFortmeyer\ActivityLog\DI\Tokens\{
    SECRETS_APP_PATH,
    SECRETS_CLIENT,
    SECRETS_DIR,
    SECRETS_LOGIN_PATH,
    SECRETS_SERVICE_HOST,
    SECRETS_SERVICE_PORT
};

return [
    SECRETS_CLIENT => new Client([
        "base_uri" => sprintf(
            "%s:%s/v1/",
            (string) getenv(SECRETS_SERVICE_HOST),
            (string) getenv(SECRETS_SERVICE_PORT)
        )
    ]),
    SecretsClient::class => static fn(ContainerInterface $container) => new SecretsClient(
        client: new ServiceProvider($container)->secretsClientDep,
        logger: new ServiceProvider($container)->logger,
        secretServiceAppPath: join("/", [getenv(SECRETS_APP_PATH), "activity-log"]),
        secretServiceLoginPath: join("/", [getenv(SECRETS_LOGIN_PATH), "activity-log-application"]),
        loginPasswdFilename: join(DIRECTORY_SEPARATOR, [
            getenv(SECRETS_DIR),
            LOGIN_PASSWD
        ]),
        tokenTtl: 3600, // 1h
        valueTtl: 1500, // 15m
    ),
];
