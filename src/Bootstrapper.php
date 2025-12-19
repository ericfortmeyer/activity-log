<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Phpolar\App;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Bootstraps the application
 */
final readonly class Bootstrapper
{
    public function __construct(
        private ContainerInterface $container,
        private ServerRequestInterface $serverRequest,
        private MiddlewareInterface $callbackMiddleware,
        private MiddlewareInterface $loginMiddleware,
        private MiddlewareInterface $logoutMiddleware,
    ) {}

    public function __invoke(): void
    {
        App::create($this->container)
            // ->useCsrfMiddleware()
            ->useAuthorization()
            ->use($this->callbackMiddleware)
            ->use($this->loginMiddleware)
            ->use($this->logoutMiddleware)
            ->receive($this->serverRequest);
    }
}
