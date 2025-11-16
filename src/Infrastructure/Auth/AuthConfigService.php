<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Infrastructure\Auth;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

class AuthConfigService
{
    private string $appRequestToken;

    public function __construct(
        private ClientInterface $client,
        private LoggerInterface $logger,
        private string $secretServiceAppPath,
        private string $secretServiceLoginPath,
        private string $loginPasswdFilename,
    ) {
        $this->appRequestToken = $this->getRequestToken();
    }

    private function getRequestToken(): string
    {
        $json = json_encode([
            "password" => trim((string)file_get_contents($this->loginPasswdFilename)),
            "token_type" => "service",
            "token_ttl" => "2h",
        ]);
        $response = $this->client->sendRequest(
            new Request(
                method: "POST",
                uri: $this->secretServiceLoginPath,
                body: $json !== false ? $json : null,
            )
        )->getBody()->getContents();
        $jsonResponse = json_decode($response, true);
        if (array_key_exists("errors", $jsonResponse)) {
            $this->logger->debug("Login error", $jsonResponse);
            return "";
        }
        return $jsonResponse["auth"]["client_token"];
    }

    private function getValue(string $key): string
    {
        $result = json_decode(
            $this->client->sendRequest(
                new Request(
                    method: "GET",
                    uri: $this->secretServiceAppPath,
                    headers: ["X-Vault-Token" => $this->appRequestToken],
                )
            )->getBody()->getContents(),
            true
        );
        if (array_key_exists("errors", $result) === true) {
            $this->logger->error("Client error", $result);
            return "";
        }
        return $result["data"]["data"][$key];
    }

    public function getClientSecret(): string
    {
        return $this->getValue("client-secret");
    }

    public function getClientId(): string
    {
        return $this->getValue("client-id");
    }

    public function getCookieSecret(): string
    {
        return $this->getValue("cookie-secret");
    }

    public function getDomain(): string
    {
        return $this->getValue("domain");
    }
}
