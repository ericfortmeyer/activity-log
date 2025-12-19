<?php

declare(strict_types=1);

use PhpContrib\Migration\MigrationInterface;
use PhpContrib\Migration\MigrationRunStatus;

/**
 * @suppress PhanUnreferencedClass
 */
final readonly class Migration1764654524155CreateTimeEntryTable implements MigrationInterface
{
    public function up(PDO $connection): MigrationRunStatus
    {
        return $connection
            ->exec(
                <<<'SQL'
                CREATE TABLE IF NOT EXISTS `time-entry`
                (
                    `id` VARCHAR(100) NOT NULL PRIMARY KEY,
                    `tenantId` VARCHAR(100) NOT NULL,
                    `dayOfMonth` TINYINT(2) NOT NULL CHECK (`dayOfMonth` > 0 && `dayOfMonth` < 32),
                    `month` TINYINT(2) NOT NULL CHECK (`month` > 0 && `month` < 13),
                    `year` YEAR(4) NOT NULL CHECK (`year` > 1900 && `year` < 3500),
                    `hours` TINYINT(2) NOT NULL CHECK (`hours` > -1 && `hours` < 25),
                    `minutes` TINYINT(2) NOT NULL CHECK (`minutes` > -1 && `minutes` < 60),
                    INDEX (`tenantId`),
                    FOREIGN KEY (`tenantId`)
                      REFERENCES tenant(`id`)
                      ON DELETE CASCADE
                );
                SQL,
            ) !== false
            && $connection
            ->exec(
                <<<'SQL'
                GRANT SELECT,INSERT,UPDATE,DELETE,DROP ON `time-entry` TO `activity_log_app`@'localhost';
                SQL
            ) !== false ? MigrationRunStatus::COMPLETED : MigrationRunStatus::FAILED;
    }

    public function down(PDO $connection): MigrationRunStatus
    {
        return $connection
            ->exec(
                <<<'SQL'
                DROP TABLE `time-entry`;
                SQL,
            ) !== false
            &&  $connection
            ->exec(
                <<<SQL
                    REVOKE IF EXISTS ALL ON `time-entry` FROM `activity_log_app`;
                    SQL
            ) !== false
            ? MigrationRunStatus::COMPLETED : MigrationRunStatus::FAILED;
    }
}
