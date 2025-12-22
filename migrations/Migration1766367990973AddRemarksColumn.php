<?php

declare(strict_types=1);

use PhpContrib\Migration\MigrationInterface;
use PhpContrib\Migration\MigrationRunStatus;

final readonly class Migration1766367990973AddRemarksColumn implements MigrationInterface
{
    public function up(PDO $connection): MigrationRunStatus
    {
        return $connection->exec(
            <<<SQL
            ALTER TABLE `remarks` ADD COLUMN `remarks` VARCHAR(4096);
            SQL,
        ) !== false ? MigrationRunStatus::COMPLETED : MigrationRunStatus::FAILED;
    }

    public function down(PDO $connection): MigrationRunStatus
    {
        return $connection->exec(
            <<<SQL
            ALTER TABLE `remarks` DROP COLUMN `remarks`;
            SQL,
        ) !== false ? MigrationRunStatus::COMPLETED : MigrationRunStatus::FAILED;
    }
}
