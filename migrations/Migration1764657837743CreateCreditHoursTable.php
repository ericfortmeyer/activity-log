<?php

declare(strict_types=1);

use PhpContrib\Migration\MigrationInterface;
use PhpContrib\Migration\MigrationRunStatus;

/**
 * @suppress PhanUnreferencedClass
 */
final readonly class Migration1764657837743CreateCreditHoursTable implements MigrationInterface
{
    public function up(PDO $connection): MigrationRunStatus
    {
        return $connection
            ->exec(
                <<<SQL
                CREATE TABLE IF NOT EXISTS`credit-hours`
                (
                    `id` VARCHAR(100) NOT NULL PRIMARY KEY,
                    `tenantId` VARCHAR(100) NOT NULL,
                    `hours` TINYINT(2) CHECK (`hours` > -1 && `hours` < 25),
                    `month` TINYINT(2) CHECK (`month` > 0 && `month` < 13),
                    `year` YEAR(4) CHECK (`year` > 1900 && `year` < 3500),
                    INDEX (`tenantId`),
                    FOREIGN KEY (`tenantId`)
                      REFERENCES tenant(`id`)
                      ON DELETE CASCADE
                );
                SQL,
            ) !== false
            &&  $connection
            ->exec(
                <<<SQL
                GRANT SELECT,INSERT,UPDATE,DELETE ON `credit-hours` TO `activity_log_app`@'localhost';
                SQL
            ) !== false ? MigrationRunStatus::COMPLETED : MigrationRunStatus::FAILED;
    }

    public function down(PDO $connection): MigrationRunStatus
    {
        return $connection
            ->exec(
                <<<SQL
                DROP TABLE `credit-hours`;
                SQL,
            ) !== false
            && $connection
            ->exec(
                <<<SQL
                    REVOKE IF EXISTS ALL ON `credit-hours` FROM `activity_log_app`;
                    SQL
            ) !== false
            ? MigrationRunStatus::COMPLETED : MigrationRunStatus::FAILED;
    }
}
