<?php

declare(strict_types=1);

use Auth0\SDK\Configuration\SdkConfiguration;
use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use EricFortmeyer\ActivityLog\Http\UnauthorizedHandler;
use PhpContrib\Authenticator\AuthenticatorInterface;
use Psr\Container\ContainerInterface;
use EricFortmeyer\ActivityLog\Infrastructure\Auth\{
    Auth0Adapter,
    AuthConfigService,
    CallbackMiddleware,
    LoginMiddleware,
    LogoutMiddleware,
};
use Phpolar\Phpolar\DependencyInjection\DiTokens;

use const EricFortmeyer\ActivityLog\DI\Tokens\{
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
        $serviceProvider = new ServiceProvider($container);

        return new AuthConfigService(
            secretsClient: $serviceProvider->secretsClient,
        );
    },
    AUTH_CLIENT_ID => static function (ContainerInterface $container): string {
        $serviceProvider = new ServiceProvider($container);
        $logger = $serviceProvider->logger;
        $authConfigService = $serviceProvider->authConfigService;

        if (\apcu_exists(AUTH_CLIENT_ID) === true) {
            $fetched = \apcu_fetch(AUTH_CLIENT_ID);
            if ($fetched !== false) {
                if (getenv("ACTIVITY_LOG_CACHE_ENABLED")) {
                    $logger->info("Returned cached client id");
                }
                return (string) $fetched;
            }
        }

        $authClientId = $authConfigService->getClientId();
        \apcu_store(AUTH_CLIENT_ID, $authClientId);
        return $authClientId;
    },
    AUTH_CLIENT_SECRET => static function (ContainerInterface $container): string {
        $serviceProvider = new ServiceProvider($container);
        $logger = $serviceProvider->logger;
        $authConfigService = $serviceProvider->authConfigService;

        if (\apcu_exists(AUTH_CLIENT_SECRET) === true) {
            $fetched = \apcu_fetch(AUTH_CLIENT_SECRET);
            if ($fetched !== false) {
                if (getenv("ACTIVITY_LOG_CACHE_ENABLED")) {
                    $logger->info("Returned cached client secret");
                }
                return (string) $fetched;
            }
        }

        $authClientSecret = $authConfigService->getClientSecret();
        \apcu_store(AUTH_CLIENT_SECRET, $authClientSecret);
        return $authClientSecret;
    },
    AUTH_COOKIE_SECRET => static function (ContainerInterface $container): string {
        $serviceProvider = new ServiceProvider($container);
        $logger = $serviceProvider->logger;
        $authConfigService = $serviceProvider->authConfigService;

        if (\apcu_exists(AUTH_COOKIE_SECRET) === true) {
            $fetched = \apcu_fetch(AUTH_COOKIE_SECRET);
            if ($fetched !== false) {
                if (getenv("ACTIVITY_LOG_CACHE_ENABLED")) {
                    $logger->info("Returned cached cookie secret");
                }
                return (string) $fetched;
            }
        }

        $authCookieSecret = $authConfigService->getCookieSecret();
        \apcu_store(AUTH_COOKIE_SECRET, $authCookieSecret);
        return $authCookieSecret;
    },
    AUTH_DOMAIN => static function (ContainerInterface $container): string {
        $serviceProvider = new ServiceProvider($container);
        $logger = $serviceProvider->logger;
        $authConfigService = $serviceProvider->authConfigService;

        if (\apcu_exists(AUTH_DOMAIN) === true) {
            $fetched = \apcu_fetch(AUTH_DOMAIN);

            if ($fetched !== false) {
                if (getenv("ACTIVITY_LOG_CACHE_ENABLED")) {
                    $logger->info("Returned cached domain value");
                }
                return (string) $fetched;
            }
        }

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
        $serviceProvider = new ServiceProvider($container);
        $logger = $serviceProvider->logger;
        $responseFactory = $serviceProvider->responseFactory;
        $appConfig = $serviceProvider->appConfig;
        $auth = $serviceProvider->auth0Adapter;

        return new LogoutMiddleware(
            auth: $auth,
            log: $logger,
            responseFactory: $responseFactory,
            appConfig: $appConfig,
        );
    },
    LOGIN_MIDDLEWARE => static function (ContainerInterface $container): LoginMiddleware {
        $serviceProvider = new ServiceProvider($container);
        $logger = $serviceProvider->logger;
        $responseFactory = $serviceProvider->responseFactory;
        $appConfig = $serviceProvider->appConfig;
        $auth = $serviceProvider->auth0Adapter;

        return new LoginMiddleware(
            auth: $auth,
            log: $logger,
            responseFactory: $responseFactory,
            appConfig: $appConfig,
        );
    },
    CALLBACK_MIDDLEWARE => static function (ContainerInterface $container): CallbackMiddleware {
        $serviceProvider = new ServiceProvider($container);
        $logger = $serviceProvider->logger;
        $responseFactory = $serviceProvider->responseFactory;
        $appConfig = $serviceProvider->appConfig;
        $auth = $serviceProvider->auth0Adapter;

        return new CallbackMiddleware(
            auth: $auth,
            log: $logger,
            responseFactory: $responseFactory,
            appConfig: $appConfig,
        );
    },
    DiTokens::UNAUTHORIZED_HANDLER => static function (ContainerInterface $container): UnauthorizedHandler {
        $serviceProvider = new ServiceProvider($container);
        $responseFactory = $serviceProvider->responseFactory;
        $appConfig = $serviceProvider->appConfig;

        return new UnauthorizedHandler(
            loginPath: $appConfig->loginPath,
            responseFactory: $responseFactory,
        );
    },
];
