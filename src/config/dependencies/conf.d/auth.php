<?php

declare(strict_types=1);

use Auth0\SDK\Configuration\SdkConfiguration;
use EricFortmeyer\ActivityLog\AppConfig;
use GuzzleHttp\Client;
use PhpContrib\Authenticator\AuthenticatorInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use EricFortmeyer\ActivityLog\Infrastructure\Auth\{
    Auth0Adapter,
    AuthConfigService,
    CallbackMiddleware,
    LoginMiddleware,
    LogoutMiddleware,
};
use GuzzleHttp\Psr7\Response;
use PhpCommonEnums\HttpResponseCode\Enumeration\HttpResponseCodeEnum as ResponseCode;
use Phpolar\Phpolar\DependencyInjection\DiTokens;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use const EricFortmeyer\ActivityLog\config\DiTokens\{
    APP_LOGIN_PATH,
    AUTH_CLIENT_ID,
    AUTH_CLIENT_SECRET,
    AUTH_COOKIE_SECRET,
    AUTH_DOMAIN,
    CALLBACK_MIDDLEWARE,
    LOGIN_MIDDLEWARE,
    LOGIN_PASSWD_FILE,
    LOGOUT_MIDDLEWARE,
    SECRETS_APP_PATH,
    SECRETS_BASE_URI,
    SECRETS_CLIENT,
    SECRETS_LOGIN_PATH,
    SECRETS_SERVICE_HOST,
    SECRETS_SERVICE_PORT
};

return [
    SECRETS_APP_PATH => static fn() => join("/", [getenv(SECRETS_APP_PATH), "activity-log"]),
    SECRETS_LOGIN_PATH => static fn() => join("/", [getenv(SECRETS_LOGIN_PATH), "activity-log-application"]),
    SECRETS_SERVICE_HOST => static fn() => getenv(SECRETS_SERVICE_HOST),
    SECRETS_SERVICE_PORT => static fn() => getenv(SECRETS_SERVICE_PORT),
    SECRETS_BASE_URI => static fn(ContainerInterface $container) => sprintf(
        "%s:%s/v1/",
        $container->get(SECRETS_SERVICE_HOST),
        $container->get(SECRETS_SERVICE_PORT)
    ),
    SECRETS_CLIENT => static fn(ContainerInterface $container) => new Client([
        "base_uri" => $container->get(SECRETS_BASE_URI)
    ]),
    AuthConfigService::class => static fn(ContainerInterface $container) => new AuthConfigService(
        client: $container->get(SECRETS_CLIENT),
        logger: $container->get(LoggerInterface::class),
        secretServiceAppPath: $container->get(SECRETS_APP_PATH),
        secretServiceLoginPath: $container->get(SECRETS_LOGIN_PATH),
        loginPasswdFilename: $container->get(LOGIN_PASSWD_FILE),
    ),
    AUTH_CLIENT_ID => static function (ContainerInterface $container): string {
        if (\apcu_exists(AUTH_CLIENT_ID) === true) {
            $fetched = \apcu_fetch(AUTH_CLIENT_ID);
            if ($fetched !== false) {
                return $fetched;
            }
        }
        /**
         * @var AuthConfigService
         */
        $authConfigService = $container->get(AuthConfigService::class);
        $authClientId = $authConfigService->getClientId();
        \apcu_store(AUTH_CLIENT_ID, $authClientId);
        return $authClientId;
    },
    AUTH_CLIENT_SECRET => static function (ContainerInterface $container): string {
        if (\apcu_exists(AUTH_CLIENT_SECRET) === true) {
            $fetched = \apcu_fetch(AUTH_CLIENT_SECRET);
            if ($fetched !== false) {
                return $fetched;
            }
        }
        /**
         * @var AuthConfigService
         */
        $authConfigService = $container->get(AuthConfigService::class);
        $authClientSecret = $authConfigService->getClientSecret();
        \apcu_store(AUTH_CLIENT_SECRET, $authClientSecret);
        return $authClientSecret;
    },
    AUTH_COOKIE_SECRET => static function (ContainerInterface $container): string {
        if (\apcu_exists(AUTH_COOKIE_SECRET) === true) {
            $fetched = \apcu_fetch(AUTH_COOKIE_SECRET);
            if ($fetched !== false) {
                return $fetched;
            }
        }
        /**
         * @var AuthConfigService
         */
        $authConfigService = $container->get(AuthConfigService::class);
        $authCookieSecret = $authConfigService->getCookieSecret();
        \apcu_store(AUTH_COOKIE_SECRET, $authCookieSecret);
        return $authCookieSecret;
    },
    AUTH_DOMAIN => static function (ContainerInterface $container): string {
        /**
         * @var LoggerInterface
         */
        $logger = $container->get(LoggerInterface::class);
        if (\apcu_exists(AUTH_DOMAIN) === true) {
            $logger->info("Returned cached domain value", ["domain" => \apcu_fetch(AUTH_DOMAIN)]);
            $fetched = \apcu_fetch(AUTH_DOMAIN);

            if ($fetched !== false) {
                return $fetched;
            }
        }
        /**
         * @var AuthConfigService
         */
        $authConfigService = $container->get(AuthConfigService::class);
        $authDomain = $authConfigService->getDomain();
        \apcu_store(AUTH_DOMAIN, $authDomain);
        return $authDomain;
    },
    AuthenticatorInterface::class => static fn(ContainerInterface $container) => new Auth0Adapter(
        new SdkConfiguration([
            "clientId" => $container->get(AUTH_CLIENT_ID),
            "clientSecret" => $container->get(AUTH_CLIENT_SECRET),
            "cookieSecret" => $container->get(AUTH_COOKIE_SECRET),
            "domain" => $container->get(AUTH_DOMAIN),
        ]),
    ),
    LOGIN_MIDDLEWARE => static fn(ContainerInterface $container) => new LoginMiddleware(
        auth: $container->get(AuthenticatorInterface::class),
        log: $container->get(LoggerInterface::class),
        responseFactory: $container->get(ResponseFactoryInterface::class),
        appConfig: $container->get(AppConfig::class),
    ),
    LOGOUT_MIDDLEWARE => static fn(ContainerInterface $container) => new LogoutMiddleware(
        auth: $container->get(AuthenticatorInterface::class),
        log: $container->get(LoggerInterface::class),
        responseFactory: $container->get(ResponseFactoryInterface::class),
        appConfig: $container->get(AppConfig::class),
    ),
    CALLBACK_MIDDLEWARE => static fn(ContainerInterface $container) => new CallbackMiddleware(
        auth: $container->get(AuthenticatorInterface::class),
        log: $container->get(LoggerInterface::class),
        responseFactory: $container->get(ResponseFactoryInterface::class),
        appConfig: $container->get(AppConfig::class),
    ),
    DiTokens::UNAUTHORIZED_HANDLER => static fn(ContainerInterface $container) =>
    new readonly class ($container->get(APP_LOGIN_PATH)) implements RequestHandlerInterface {
        public function __construct(private string $loginPath) {}
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            return new Response(
                ResponseCode::TemporaryRedirect->value,
                ["Location" => $this->loginPath]
            );
        }
    },
];
