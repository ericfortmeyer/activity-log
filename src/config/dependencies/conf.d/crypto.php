<?php

declare(strict_types=1);

use EricFortmeyer\ActivityLog\Clients\SecretsClient;
use Psr\Container\ContainerInterface;

use const EricFortmeyer\ActivityLog\config\DiTokens\HASH_KEY;

return [
    HASH_KEY => static function (ContainerInterface $container) {
        /**
         * @var SecretsClient $secretsClient
         */
        $secretsClient = $container->get(SecretsClient::class);
        return $secretsClient->getValue("hash-key");
    },
];
