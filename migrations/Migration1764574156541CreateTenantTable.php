<?php

declare(strict_types=1);

use PhpContrib\Migration\MigrationInterface;
use PhpContrib\Migration\MigrationRunStatus;

/**
 * @suppress PhanUnreferencedClass
 */
final readonly class Migration1764574156541CreateTenantTable implements MigrationInterface
{
    public function up(PDO $connection): MigrationRunStatus
    {
        return $connection
            ->exec(
                <<<SQL
                CREATE TABLE IF NOT EXISTS `tenant`
                (
                    `id` VARCHAR(100) NOT NULL PRIMARY KEY
                );
                SQL,
            ) !== false
            && $connection
            ->exec(
                <<<'SQL'
                GRANT SELECT,INSERT,UPDATE,DELETE ON `tenant` TO `activity_log_app`@'localhost';
                SQL
            ) !== false
            ? MigrationRunStatus::COMPLETED : MigrationRunStatus::FAILED;
    }

    public function down(PDO $connection): MigrationRunStatus
    {
        return $connection
            ->exec(
                <<<SQL
                DROP TABLE `tenant`;
                SQL,
            ) !== false
            &&  $connection
            ->exec(
                <<<SQL
                    REVOKE IF EXISTS ALL ON `tenant` FROM `activity_log_app`;
                    SQL
            ) !== false ? MigrationRunStatus::COMPLETED : MigrationRunStatus::FAILED;
    }
}
