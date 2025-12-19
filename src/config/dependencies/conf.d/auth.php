<?php

/**
 * @phan-file-suppress PhanUnreferencedClosure
 */

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
    CALLBACK_MIDDLEWARE,
    LOGIN_MIDDLEWARE,
    LOGOUT_MIDDLEWARE,
};

return [
    AuthConfigService::class => static fn(ContainerInterface $container) => new AuthConfigService(
        secretsClient: new ServiceProvider($container)->secretsClient,
    ),
    AuthenticatorInterface::class => static fn(ContainerInterface $container) => new Auth0Adapter(
        new SdkConfiguration([
            "clientId" => new ServiceProvider($container)->authConfigService->getClientId(),
            "clientSecret" => new ServiceProvider($container)->authConfigService->getClientSecret(),
            "cookieSecret" => new ServiceProvider($container)->authConfigService->getCookieSecret(),
            "domain" => new ServiceProvider($container)->authConfigService->getDomain(),
        ]),
    ),
    LOGOUT_MIDDLEWARE => static fn(ContainerInterface $container) =>
    new LogoutMiddleware(
        auth: new ServiceProvider($container)->auth0Adapter,
        log: new ServiceProvider($container)->logger,
        responseFactory: new ServiceProvider($container)->responseFactory,
        appConfig: new ServiceProvider($container)->appConfig,
    ),
    LOGIN_MIDDLEWARE => static fn(ContainerInterface $container) =>
    new LoginMiddleware(
        auth: new ServiceProvider($container)->auth0Adapter,
        log: new ServiceProvider($container)->logger,
        responseFactory: new ServiceProvider($container)->responseFactory,
        appConfig: new ServiceProvider($container)->appConfig,
    ),
    CALLBACK_MIDDLEWARE => static fn(ContainerInterface $container) => new CallbackMiddleware(
        auth: new ServiceProvider($container)->auth0Adapter,
        log: new ServiceProvider($container)->logger,
        responseFactory: new ServiceProvider($container)->responseFactory,
        appConfig: new ServiceProvider($container)->appConfig,
    ),
    DiTokens::UNAUTHORIZED_HANDLER => static fn(ContainerInterface $container) => new UnauthorizedHandler(
        loginPath: new ServiceProvider($container)->appConfig->loginPath,
        responseFactory: new ServiceProvider($container)->responseFactory,
    ),
];
