<?php

/**
 * @phan-file-suppress PhanUnreferencedClosure
 */

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Clients;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use EricFortmeyer\ActivityLog\DI\ValueProvider;
use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;

use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_CLIENT;

return [
    SECRETS_CLIENT => new Client([
        "base_uri" => new ValueProvider()->secretsBaseUri,
    ]),
    SecretsClient::class => static fn(ContainerInterface $container) => new SecretsClient(
        client: new ServiceProvider($container)->secretsClientDep,
        logger: new ServiceProvider($container)->logger,
        secretServiceAppPath: new ValueProvider()->secretsServiceAppPath,
        secretServiceLoginPath: new ValueProvider()->secretsServiceLoginPath,
        loginPasswdFilename: new ValueProvider()->loginPasswdFilename,
        secretsCacheEnabled: new ValueProvider()->secretsCacheEnabled,
        tokenTtl: new ValueProvider()->secretsTokenTtl,
        valueTtl: new ValueProvider()->secretsValueTtl,
    ),
];
