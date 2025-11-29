<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\AppConfig;
use Phpolar\SqliteStorage\SqliteReadOnlyStorage;

class AppConfigService
{
    public function __construct(
        private readonly SqliteReadOnlyStorage $sqliteStorage,
    ) {}

    public function get(): AppConfig|false
    {
        /**
         * @var array<int,array<string,string>>
         */
        $configValues = $this->sqliteStorage->findAll();

        return count($configValues) > 0 ? new AppConfig($configValues[0]) : false;
    }
}
