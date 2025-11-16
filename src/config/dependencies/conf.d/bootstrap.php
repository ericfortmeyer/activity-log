<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Phpolar\App;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

use const EricFortmeyer\ActivityLog\config\DiTokens\{
    BOOTSTRAPPER,
    CALLBACK_MIDDLEWARE,
    LOGIN_MIDDLEWARE,
    LOGOUT_MIDDLEWARE
};

return [
    BOOTSTRAPPER => static fn(ContainerInterface $container) =>
    static fn() =>
    /**
     *
     * Configure the web application
     * ==========================================================
     */
    App::create($container)
        ->useCsrfMiddleware()
        ->useAuthorization()
        ->use($container->get(LOGIN_MIDDLEWARE))
        ->use($container->get(LOGOUT_MIDDLEWARE))
        ->use($container->get(CALLBACK_MIDDLEWARE))
        ->receive($container->get(ServerRequestInterface::class))
];
