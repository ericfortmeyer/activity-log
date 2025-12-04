<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use EricFortmeyer\ActivityLog\Migrations\MigrationRunnerInterface;
use Phpolar\Phpolar\App;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Bootstraps the application
 */
final readonly class Bootstrapper
{
    private const ON = ["1", "true", "TRUE"];
    private bool $shouldRunMigrations;

    public function __construct(
        private string $shouldRunMigrationsValue,
        private ContainerInterface $container,
        private ServerRequestInterface $serverRequest,
        private MiddlewareInterface $callbackMiddleware,
        private MiddlewareInterface $loginMiddleware,
        private MiddlewareInterface $logoutMiddleware,
        private MigrationRunnerInterface $migrationRunner,
    ) {
        $this->shouldRunMigrations = \in_array($this->shouldRunMigrationsValue, self::ON);
    }

    public function __invoke(): void
    {
        if ($this->shouldRunMigrations === true) {
            $this->migrationRunner->run();
        }

        App::create($this->container)
            ->useCsrfMiddleware()
            ->useAuthorization()
            ->use($this->callbackMiddleware)
            ->use($this->loginMiddleware)
            ->use($this->logoutMiddleware)
            ->receive($this->serverRequest);
    }
}
