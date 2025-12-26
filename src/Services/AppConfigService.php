<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\AppConfig;
use Phpolar\Storage\StorageContext;
use SQLite3;

class AppConfigService
{
    /**
     * @param StorageContext<array<string,string>> $readStorage
     */
    public function __construct(
        private readonly StorageContext $readStorage,
        private readonly SQLite3 $writeConnection,
    ) {}

    public function get(): AppConfig|false
    {
        /**
         * @var array<int,array<string,string>>
         */
        $configValues = $this->readStorage->findAll();

        return count($configValues) > 0 ? new AppConfig($configValues[0]) : false;
    }

    public function updateVersion(
        string $version
    ): bool {
        $currentConfig = $this->get();

        if ($currentConfig === false) {
            return false;
        }

        $currentConfig->version = $version;

        if ($currentConfig->isValid() === false) {
            return false;
        }

        $stmt = $this->writeConnection->prepare(
            <<<SQL
            UPDATE activity_log_config
            SET "version"=:version
            WHERE "id"=:id;
            SQL,
        );

        return $stmt !== false
            && $stmt->bindValue("id", $currentConfig->id) !== false
            && $stmt->bindValue("version", $version) !== false
            && $stmt->execute() !== false;
    }
}
