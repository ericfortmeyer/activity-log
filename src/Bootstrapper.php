<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\Infrastructure\Auth\CallbackMiddleware;
use EricFortmeyer\ActivityLog\Infrastructure\Auth\LoginMiddleware;
use EricFortmeyer\ActivityLog\Infrastructure\Auth\LogoutMiddleware;
use Phpolar\Phpolar\App;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

use const EricFortmeyer\ActivityLog\config\DiTokens\CALLBACK_MIDDLEWARE;
use const EricFortmeyer\ActivityLog\config\DiTokens\LOGIN_MIDDLEWARE;
use const EricFortmeyer\ActivityLog\config\DiTokens\LOGOUT_MIDDLEWARE;

/**
 * Bootstraps the application
 */
final readonly class Bootstrapper
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function __invoke(): void
    {
        App::create($this->container)
            ->useCsrfMiddleware()
            ->useAuthorization()
            ->use($this->getCallbackMiddleWare())
            ->use($this->getLoginMiddleWare())
            ->use($this->getLogoutMiddleWare())
            ->receive($this->getRequest());
    }

    public function getRequest(): ServerRequestInterface
    {
        /**
         * @var ServerRequestInterface
         */
        $serverRequest = $this->container->get(ServerRequestInterface::class);
        return $serverRequest;
    }

    public function getLoginMiddleWare(): LoginMiddleware
    {
        /**
         * @var LoginMiddleware
         */
        $loginMiddleware = $this->container->get(LOGIN_MIDDLEWARE);
        return $loginMiddleware;
    }

    public function getLogoutMiddleWare(): LogoutMiddleware
    {
        /**
         * @var LogoutMiddleware
         */
        $logoutMiddleware = $this->container->get(LOGOUT_MIDDLEWARE);
        return $logoutMiddleware;
    }

    public function getCallbackMiddleWare(): CallbackMiddleware
    {
        /**
         * @var CallbackMiddleware
         */
        $callbackMiddleware = $this->container->get(CALLBACK_MIDDLEWARE);
        return $callbackMiddleware;
    }
}
