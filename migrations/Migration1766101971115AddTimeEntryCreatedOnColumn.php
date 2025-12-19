<?php

declare(strict_types=1);

use PhpContrib\Migration\MigrationInterface;
use PhpContrib\Migration\MigrationRunStatus;

final readonly class Migration1766101971115AddTimeEntryCreatedOnColumn implements MigrationInterface
{
    public function up(PDO $connection): MigrationRunStatus
    {
        return $connection->exec(
            <<<SQL
            ALTER TABLE `time-entry` ADD COLUMN `createdOn` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
            SQL,
        ) !== false ? MigrationRunStatus::COMPLETED : MigrationRunStatus::FAILED;
    }

    public function down(PDO $connection): MigrationRunStatus
    {
        return $connection->exec(
            <<<SQL
            ALTER TABLE `time-entry` DROP COLUMN `createdOn`;
            SQL,
        ) !== false ? MigrationRunStatus::COMPLETED : MigrationRunStatus::FAILED;
    }
}
