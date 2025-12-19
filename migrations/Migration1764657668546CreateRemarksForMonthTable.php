<?php

declare(strict_types=1);

use PhpContrib\Migration\MigrationInterface;
use PhpContrib\Migration\MigrationRunStatus;

/**
 * @suppress PhanUnreferencedClass
 */
final readonly class Migration1764657668546CreateRemarksForMonthTable implements MigrationInterface
{
    public function up(PDO $connection): MigrationRunStatus
    {
        $tableCreateResult = $connection
            ->exec(
                <<<SQL
                CREATE TABLE IF NOT EXISTS `remarks`
                (
                    `id` VARCHAR(100) NOT NULL PRIMARY KEY,
                    `tenantId` VARCHAR(100) NOT NULL,
                    `month` TINYINT(2) CHECK (`month` > 0 && `month` < 13),
                    `year` YEAR(4) CHECK (`year` > 1900 && `year` < 3500),
                    INDEX (`tenantId`),
                    FOREIGN KEY (`tenantId`)
                      REFERENCES tenant(`id`)
                      ON DELETE CASCADE
                );
                SQL,
            );
        $grantResult = $connection
            ->exec(
                <<<SQL
                GRANT SELECT,INSERT,UPDATE,DELETE ON `remarks` TO `activity_log_app`@'localhost';
                SQL
            );
        return $tableCreateResult !== false && $grantResult !== false
            ? MigrationRunStatus::COMPLETED
            : MigrationRunStatus::FAILED;
    }

    public function down(PDO $connection): MigrationRunStatus
    {
        return $connection
            ->exec(
                <<<SQL
                DROP TABLE `remarks`;
                SQL,
            ) !== false
            &&  $connection
            ->exec(
                <<<SQL
                    REVOKE IF EXISTS ALL ON `remarks` FROM `activity_log_app`;
                    SQL
            ) !== false
            ? MigrationRunStatus::COMPLETED
            : MigrationRunStatus::FAILED;
    }
}
