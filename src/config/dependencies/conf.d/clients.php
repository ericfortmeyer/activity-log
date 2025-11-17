<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Clients;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;

use const EricFortmeyer\ActivityLog\config\DiTokens\{
    LOGIN_PASSWD_FILE,
    SECRETS_APP_PATH,
    SECRETS_BASE_URI,
    SECRETS_CLIENT,
    SECRETS_LOGIN_PATH,
    SECRETS_SERVICE_HOST,
    SECRETS_SERVICE_PORT
};

return [
    SECRETS_APP_PATH => static fn() => join("/", [getenv(SECRETS_APP_PATH), "activity-log"]),
    SECRETS_BASE_URI => static fn(ContainerInterface $container) => sprintf(
        "%s:%s/v1/",
        $container->get(SECRETS_SERVICE_HOST),
        $container->get(SECRETS_SERVICE_PORT)
    ),
    SECRETS_CLIENT => static fn(ContainerInterface $container) => new Client([
        "base_uri" => $container->get(SECRETS_BASE_URI)
    ]),
    SecretsClient::class => static fn(ContainerInterface $container) => new SecretsClient(
        client: $container->get(SECRETS_CLIENT),
        logger: $container->get(LoggerInterface::class),
        secretServiceAppPath: $container->get(SECRETS_APP_PATH),
        secretServiceLoginPath: $container->get(SECRETS_LOGIN_PATH),
        loginPasswdFilename: $container->get(LOGIN_PASSWD_FILE),
        tokenTtl: 3600, // 1h
        valueTtl: 1500, // 15m
    ),
    SECRETS_LOGIN_PATH => static fn() => join("/", [getenv(SECRETS_LOGIN_PATH), "activity-log-application"]),
    SECRETS_SERVICE_HOST => static fn() => getenv(SECRETS_SERVICE_HOST),
    SECRETS_SERVICE_PORT => static fn() => getenv(SECRETS_SERVICE_PORT),
];
