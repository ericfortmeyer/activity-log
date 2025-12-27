<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Phpolar\App;
use Phpolar\Phpolar\ExceptionHandlerInterface;
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
        private MiddlewareInterface $eventHooksMiddleware,
        private ExceptionHandlerInterface $exceptionHandler,
    ) {
        ini_set("display_errors", false);
        ini_set("session.name", "activity-log-app");
        // ini_set("session.cache_limiter", "private_no_expire");
    }

    public function __invoke(): void
    {
        App::create($this->container)
            // ->useCsrfMiddleware()
            ->useAuthorization()
            ->useExceptionHandler($this->exceptionHandler)
            ->use($this->eventHooksMiddleware)
            ->use($this->callbackMiddleware)
            ->use($this->loginMiddleware)
            ->use($this->logoutMiddleware)
            ->receive($this->serverRequest);
    }
}
