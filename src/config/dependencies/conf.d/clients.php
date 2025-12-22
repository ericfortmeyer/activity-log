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
use GuzzleHttp\RequestOptions;

use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_TLS_CLIENT;

return [
    SECRETS_TLS_CLIENT => new Client([
        "base_uri" => new ValueProvider()->secretsTlsBaseUri,
        RequestOptions::SSL_KEY => new ValueProvider()->sslKey,
        RequestOptions::CERT => new ValueProvider()->sslCert,
    ]),
    SecretsClient::class => static fn(ContainerInterface $container) => new SecretsClient(
        tlsClient: new ServiceProvider($container)->secretsTlsClientDep,
        logger: new ServiceProvider($container)->logger,
        secretServiceAppPath: new ValueProvider()->secretsServiceAppPath,
        secretServiceLoginPath: new ValueProvider()->secretsServiceLoginPath,
        secretsUser: new ValueProvider()->secretsUser,
        secretsCacheEnabled: new ValueProvider()->secretsCacheEnabled,
        tokenTtl: new ValueProvider()->secretsTokenTtl,
        valueTtl: new ValueProvider()->secretsValueTtl,
    ),
];
