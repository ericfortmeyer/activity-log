<?php

declare(strict_types=1);

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use EricFortmeyer\ActivityLog\ExceptionHandler;
use EricFortmeyer\ActivityLog\UserInterface\Contexts\ServerErrorContext;
use Phpolar\Phpolar\DependencyInjection\DiTokens;
use Phpolar\Phpolar\ExceptionHandlerInterface;
use Psr\Container\ContainerInterface;

return [
    ExceptionHandlerInterface::class => static fn(ContainerInterface $container) =>
    new ExceptionHandler(
        logger: new ServiceProvider($container)->exceptionLogger
    ),
    DiTokens::SERVER_ERROR_RESPONSE_CONTENT => static fn(ContainerInterface $container) =>
    new ServiceProvider($container)->templateEngine
        ->apply(
            "500",
            new ServerErrorContext(
                message: "An error occurred. We are investigating."
            )
        ),
];
