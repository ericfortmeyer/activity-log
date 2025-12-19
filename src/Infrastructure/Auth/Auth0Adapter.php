<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Infrastructure\Auth;

use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Contract\{
    API\AuthenticationInterface,
    API\ManagementInterface,
    Auth0Interface,
    TokenInterface
};
use PhpContrib\Authenticator\AuthenticatorInterface;
use Exception;

/**
 * @phan-file-suppress PhanUnusedPublicFinalMethodParameter
 * @phan-file-suppress PhanUnusedPublicMethodParameter
 * @codeCoverageIgnore
 */
readonly class Auth0Adapter implements Auth0Interface, AuthenticatorInterface
{
    private Auth0 $auth;

    public function __construct(SdkConfiguration $configuration)
    {
        $this->auth = new Auth0($configuration);
    }

    public function authentication(): AuthenticationInterface
    {
        return $this->auth->authentication();
    }

    public function clear(bool $transient = true): Auth0Interface
    {
        return $this->auth->clear($transient);
    }

    public function configuration(): SdkConfiguration
    {
        return $this->auth->configuration();
    }

    public function decode(
        string $token,
        ?array $tokenAudience = null,
        ?array $tokenOrganization = null,
        ?string $tokenNonce = null,
        ?int $tokenMaxAge = null,
        ?int $tokenLeeway = null,
        ?int $tokenNow = null,
        ?int $tokenType = null
    ): TokenInterface {
        return $this->auth->decode(
            $token,
            $tokenAudience,
            $tokenOrganization,
            $tokenNonce,
            $tokenMaxAge,
            $tokenLeeway,
            $tokenNow,
            $tokenType
        );
    }

    public function exchange(?string $redirectUri = null, ?string $code = null, ?string $state = null): bool
    {
        return $this->auth->exchange($redirectUri, $code, $state);
    }

    /**
     * @throws Exception
     */
    public function handleBackchannelLogout(string $logoutToken): TokenInterface
    {
        throw new Exception("Not implemented yet");
    }

    /**
     * @throws Exception
     */
    public function getBackchannel(): ?string
    {
        throw new Exception("Not implemented yet");
    }

    /**
     * @throws Exception
     */
    public function setBackchannel(string $backchannel): Auth0Interface
    {
        throw new Exception("Not implemented yet");
    }

    /**
     * @throws Exception
     */
    public function getAccessToken(): ?string
    {
        throw new Exception("Not implemented yet");
    }

    /**
     * @throws Exception
     */
    public function getAccessTokenExpiration(): ?int
    {
        throw new Exception("Not implemented yet");
    }

    /**
     * @throws Exception
     */
    public function getAccessTokenScope(): ?array
    {
        throw new Exception("Not implemented yet");
    }

    /**
     * @throws Exception
     */
    public function getBearerToken(
        ?array $get = null,
        ?array $post = null,
        ?array $server = null,
        ?array $haystack = null,
        ?array $needles = null
    ): ?TokenInterface {
        throw new Exception("Not implemented yet");
    }

    /**
     * @throws Exception
     */
    public function getExchangeParameters(): ?object
    {
        throw new Exception("Not implemented yet");
    }

    /**
     * @throws Exception
     */
    public function getIdToken(): ?string
    {
        throw new Exception("Not implemented yet");
    }

    /**
     * @throws Exception
     */
    public function getInvitationParameters(): ?array
    {
        throw new Exception("Not implemented yet");
    }

    /**
     * @throws Exception
     */
    public function getRefreshToken(): ?string
    {
        throw new Exception("Not implemented yet");
    }

    /**
     * @throws Exception
     */
    public function getRequestParameter(
        string $parameterName,
        int $filter = FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        array $filterOptions = []
    ): ?string {
        throw new Exception("Not implemented yet");
    }

    public function getUser(): ?array
    {
        return $this->auth->getUser();
    }

    /**
     * @throws Exception
     */
    public function handleInvitation(?string $redirectUrl = null, ?array $params = null): ?string
    {
        throw new Exception("Not implemented yet");
    }

    public function isAuthenticated(): bool
    {
        return $this->auth->isAuthenticated();
    }

    public function logout(?string $returnUri = null, ?array $params = null): string
    {
        return $this->auth->logout($returnUri, $params);
    }

    public function login(?string $redirectUrl = null, ?array $params = null): string
    {
        return $this->auth->login($redirectUrl, $params);
    }

    public function management(): ManagementInterface
    {
        return $this->auth->management();
    }

    public function refreshState(): Auth0Interface
    {
        return $this->auth->refreshState();
    }

    public function renew(?array $params = null): Auth0Interface
    {
        return $this->auth->renew($params);
    }

    public function setAccessToken(string $accessToken): Auth0Interface
    {
        return $this->auth->setAccessToken($accessToken);
    }

    /**
     * @throws Exception
     */
    public function setAccessTokenExpiration(int $accessTokenExpiration): Auth0Interface
    {
        throw new Exception("Not implemented yet");
    }

    /**
     * @throws Exception
     */
    public function setAccessTokenScope(array $accessTokenScope): Auth0Interface
    {
        throw new Exception("Not implemented yet");
    }

    public function setConfiguration(SdkConfiguration|array $configuration): Auth0Interface
    {
        return $this->auth->setConfiguration($configuration);
    }

    public function setIdToken(string $idToken): Auth0Interface
    {
        return $this->auth->setIdToken($idToken);
    }

    public function setRefreshToken(string $refreshToken): Auth0Interface
    {
        return $this->auth->setRefreshToken($refreshToken);
    }

    public function setUser(array $user): Auth0Interface
    {
        return $this->auth->setUser($user);
    }

    public function signup(?string $redirectUrl = null, ?array $params = null): string
    {
        return $this->auth->signup($redirectUrl, $params);
    }

    public function getCredentials(): ?object
    {
        return $this->auth->getCredentials();
    }
}
