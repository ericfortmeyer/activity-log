<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\AppConfig;
use Phpolar\Storage\StorageContext;

class AppConfigService
{
    /**
     * @param StorageContext<array<string,string>> $storage
     */
    public function __construct(
        private readonly StorageContext $storage,
    ) {}

    public function get(): AppConfig|false
    {
        /**
         * @var array<int,array<string,string>>
         */
        $configValues = $this->storage->findAll();

        return count($configValues) > 0 ? new AppConfig($configValues[0]) : false;
    }
}
