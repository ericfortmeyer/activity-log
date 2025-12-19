<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Clients;

use GuzzleHttp\Psr7\Request;
use PhpCommonEnums\HttpResponseCode\Enumeration\HttpResponseCodeEnum as HttpResponse;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use SensitiveParameter;

/**
 * Retrieves secrets from the store.
 *
 * Values are cached
 *
 * @codeCoverageIgnore
 */
readonly class SecretsClient
{
    private const AUTH_CLIENT_TOKEN_CACHE_KEY = "auth_client_token";

    public function __construct(
        private ClientInterface $client,
        private LoggerInterface $logger,
        #[SensitiveParameter]
        private string $secretServiceAppPath,
        #[SensitiveParameter]
        private string $secretServiceLoginPath,
        #[SensitiveParameter]
        private string $loginPasswdFilename,
        private bool $secretsCacheEnabled,
        private int $tokenTtl,
        private int $valueTtl,
    ) {}

    public function getValue(string $key): string
    {
        if (\apcu_exists($key)) {
            $fetchedValue = \apcu_fetch($key);
            if ($fetchedValue !== false) {
                // the value is cached, return it
                if ($this->secretsCacheEnabled) {
                    $this->logger->info("Cached value fetched", ["key" => $key]);
                }
                return (string) $fetchedValue;
            }
        }

        // the value is not cached, let's request it
        if (\apcu_exists(self::AUTH_CLIENT_TOKEN_CACHE_KEY)) {
            $authClientToken = \apcu_fetch(self::AUTH_CLIENT_TOKEN_CACHE_KEY);
            if ($authClientToken !== false) {
                $valueResponse = $this->client->sendRequest(
                    new Request(
                        method: "GET",
                        uri: $this->secretServiceAppPath,
                        headers: ["X-Vault-Token" => $authClientToken],
                    )
                );

                if ($valueResponse->getStatusCode() === HttpResponse::Ok->value) {
                    $response = json_decode(
                        $valueResponse->getBody()->getContents(),
                        true,
                    );

                    if (\is_array($response) === false) {
                        return "";
                    }

                    if (array_key_exists("errors", $response) === true) {
                        $this->logger->error("Client error", $response);
                        return "";
                    }

                    if (\array_key_exists("data", $response)) {
                        // success!, cache and return the value
                        $value = $response["data"]["data"][$key];
                        \apcu_store(
                            $key,
                            $value,
                            $this->valueTtl,
                        );

                        return (string) $value;
                    }
                }

                // we encountered an error retrieving the secret, report it
                $this->logger->critical(
                    "An error occurred when retrieving $key",
                    [
                        "response code" => $valueResponse->getStatusCode(),
                        "reason" => $valueResponse->getReasonPhrase(),
                        "body" => $valueResponse->getBody()->getContents(),
                    ]
                );
                return "";
            }
        }

        // The client token was not cached so let's fetch it.

        $password = file_get_contents($this->loginPasswdFilename);
        if ($password === false) {
            $errors = error_get_last();
            $this->logger->critical("File read error", $errors !== null ?  $errors : []);
            // we can't login to the secret store, abort
            return "";
        }

        $json = json_encode([
            "password" => trim($password),
            "token_type" => "service",
            "token_ttl" => "1h",
        ]);
        $authClientTokenResponse = $this->client->sendRequest(
            new Request(
                method: "POST",
                uri: $this->secretServiceLoginPath,
                body: $json !== false ? $json : null,
            )
        );

        if ($authClientTokenResponse->getStatusCode() === HttpResponse::Ok->value) {
            $jsonResponse = json_decode($authClientTokenResponse->getBody()->getContents(), true);

            if (\is_array($jsonResponse) === false) {
                return "";
            }

            if (array_key_exists("errors", $jsonResponse)) {
                $this->logger->critical("Login error", $jsonResponse);
                return "";
            }

            if (\array_key_exists("auth", $jsonResponse) === false) {
                return "";
            }

            $authClientToken = $jsonResponse["auth"]["client_token"];

            // cache the client token
            \apcu_store(
                self::AUTH_CLIENT_TOKEN_CACHE_KEY,
                $authClientToken,
                $this->tokenTtl,
            );

            $valueResponse = $this->client->sendRequest(
                new Request(
                    method: "GET",
                    uri: $this->secretServiceAppPath,
                    headers: ["X-Vault-Token" => $authClientToken],
                )
            );

            if ($valueResponse->getStatusCode() === HttpResponse::Ok->value) {
                $result = json_decode(
                    $valueResponse->getBody()->getContents(),
                    true,
                );

                if (\is_array($result) === false) {
                    return "";
                }

                if (array_key_exists("errors", $result) === true) {
                    $this->logger->error("JSON decode error", $result);
                    return "";
                }

                if (\array_key_exists("data", $result) === false) {
                    return "";
                }

                // success! cache and return the value
                $value = $result["data"]["data"][$key];

                \apcu_store(
                    $key,
                    $value,
                    $this->valueTtl,
                );
                return (string) $value;
            }
            // we encountered an error retrieving the secret, report it
            $this->logger->critical(
                "An error occurred when retrieving $key",
                [
                    "response code" => $valueResponse->getStatusCode(),
                    "reason" => $valueResponse->getReasonPhrase(),
                    "body" => $valueResponse->getBody()->getContents(),
                ]
            );
            return "";
        }

        // we encountered an error retrieving the auth client token, report it
        $this->logger->critical(
            "An error occurred when retrieving $key",
            [
                "response code" => $authClientTokenResponse->getStatusCode(),
                "reason" => $authClientTokenResponse->getReasonPhrase(),
                "body" => $authClientTokenResponse->getBody()->getContents(),
            ]
        );
        return "";
    }
}
