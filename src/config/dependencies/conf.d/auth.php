<?php

declare(strict_types=1);

use Auth0\SDK\Configuration\SdkConfiguration;
use EricFortmeyer\ActivityLog\AppConfig;
use EricFortmeyer\ActivityLog\Clients\SecretsClient;
use EricFortmeyer\ActivityLog\Http\UnauthorizedHandler;
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
use Phpolar\Phpolar\DependencyInjection\DiTokens;

use const EricFortmeyer\ActivityLog\config\DiTokens\{
    APP_LOGIN_PATH,
    AUTH_CLIENT_ID,
    AUTH_CLIENT_SECRET,
    AUTH_COOKIE_SECRET,
    AUTH_DOMAIN,
    CALLBACK_MIDDLEWARE,
    LOGIN_MIDDLEWARE,
    LOGOUT_MIDDLEWARE,
};

return [
    AuthConfigService::class => static function (ContainerInterface $container) {
        /**
         * @var SecretsClient
         */
        $secretsClient = $container->get(SecretsClient::class);
        return new AuthConfigService(
            secretsClient: $secretsClient,
        );
    },
    AUTH_CLIENT_ID => static function (ContainerInterface $container): string {
        /**
         * @var LoggerInterface
         */
        $logger = $container->get(LoggerInterface::class);
        if (\apcu_exists(AUTH_CLIENT_ID) === true) {
            $fetched = \apcu_fetch(AUTH_CLIENT_ID);
            if ($fetched !== false) {
                if (getenv("ACTIVITY_LOG_CACHE_ENABLED")) {
                    $logger->info("Returned cached client id");
                }
                return (string) $fetched;
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
        /**
         * @var LoggerInterface
         */
        $logger = $container->get(LoggerInterface::class);
        if (\apcu_exists(AUTH_CLIENT_SECRET) === true) {
            $fetched = \apcu_fetch(AUTH_CLIENT_SECRET);
            if ($fetched !== false) {
                if (getenv("ACTIVITY_LOG_CACHE_ENABLED")) {
                    $logger->info("Returned cached client secret");
                }
                return (string) $fetched;
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
        /**
         * @var LoggerInterface
         */
        $logger = $container->get(LoggerInterface::class);
        if (\apcu_exists(AUTH_COOKIE_SECRET) === true) {
            $fetched = \apcu_fetch(AUTH_COOKIE_SECRET);
            if ($fetched !== false) {
                if (getenv("ACTIVITY_LOG_CACHE_ENABLED")) {
                    $logger->info("Returned cached cookie secret");
                }
                return (string) $fetched;
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
            $fetched = \apcu_fetch(AUTH_DOMAIN);

            if ($fetched !== false) {
                if (getenv("ACTIVITY_LOG_CACHE_ENABLED")) {
                    $logger->info("Returned cached domain value");
                }
                return (string) $fetched;
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
    LOGOUT_MIDDLEWARE => static function (ContainerInterface $container): LogoutMiddleware {
        /**
         * @var Auth0Adapter
         */
        $auth = $container->get(AuthenticatorInterface::class);
        /**
         * @var LoggerInterface
         */
        $log = $container->get(LoggerInterface::class);
        /**
         * @var ResponseFactoryInterface
         */
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        /**
         * @var AppConfig
         */
        $appConfig = $container->get(AppConfig::class);
        return new LogoutMiddleware(
            auth: $auth,
            log: $log,
            responseFactory: $responseFactory,
            appConfig: $appConfig,
        );
    },
    LOGIN_MIDDLEWARE => static function (ContainerInterface $container): LoginMiddleware {
        /**
         * @var Auth0Adapter
         */
        $auth = $container->get(AuthenticatorInterface::class);
        /**
         * @var LoggerInterface
         */
        $log = $container->get(LoggerInterface::class);
        /**
         * @var ResponseFactoryInterface
         */
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        /**
         * @var AppConfig
         */
        $appConfig = $container->get(AppConfig::class);
        return new LoginMiddleware(
            auth: $auth,
            log: $log,
            responseFactory: $responseFactory,
            appConfig: $appConfig,
        );
    },
    CALLBACK_MIDDLEWARE => static function (ContainerInterface $container): CallbackMiddleware {
        /**
         * @var Auth0Adapter
         */
        $auth = $container->get(AuthenticatorInterface::class);
        /**
         * @var LoggerInterface
         */
        $log = $container->get(LoggerInterface::class);
        /**
         * @var ResponseFactoryInterface
         */
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        /**
         * @var AppConfig
         */
        $appConfig = $container->get(AppConfig::class);
        return new CallbackMiddleware(
            auth: $auth,
            log: $log,
            responseFactory: $responseFactory,
            appConfig: $appConfig,
        );
    },
    DiTokens::UNAUTHORIZED_HANDLER => static function (ContainerInterface $container): UnauthorizedHandler {
        /**
         * @var AppConfig
         */
        $appConfig = $container->get(AppConfig::class);
        /**
         * @var ResponseFactoryInterface
         */
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        return new UnauthorizedHandler(
            loginPath: $appConfig->loginPath,
            responseFactory: $responseFactory,
        );
    },
];
