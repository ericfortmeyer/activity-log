<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\Tenant;
use PDO;

/**
 * Handle tenant storage operations
 *
 * We are not using a StorageContext
 * here because the library does not
 * currently support single column
 * tables.
 */
readonly class TenantService
{
    public function __construct(
        private PDO $connection,
    )
    {
    }

    public function exists(string $tenantId): bool
    {
        $exists = false;

        $stmt = $this->connection->prepare(
            <<<SQL
            SELECT EXISTS (
                SELECT 1 FROM `tenant` WHERE `id`=:id LIMIT 1
            )
            SQL,
        );

        if ($stmt === false) {
            return false;
        }

        $stmt->bindColumn(1, $exists, PDO::PARAM_BOOL);
        $stmt->execute(["id" => $tenantId]);
        $stmt->fetch();

        return $exists;
    }

    /**
     * @return Tenant[]
     */
    public function getAll(): array
    {
        $stmt = $this->connection->query(
            <<<SQL
            TABLE `tenant`;
            SQL,
            PDO::FETCH_CLASS
        );

        if ($stmt === false) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function save(Tenant $tenant): void
    {
        $stmt = $this->connection->prepare(
            <<<SQL
            INSERT INTO `tenant`
            VALUES(:id)
            SQL,
        );

        if ($stmt === false) {
            return;
        }

        // make sure tenant id is already hashed
        $stmt->execute(["id" => $tenant->getPrimaryKey()]);
    }

    public function deleteAllData(string $tenantId): void
    {
        $stmt = $this->connection->prepare(
            <<<SQL
            DELETE FROM `tenant`
            WHERE id=:id
            SQL,
        );

        if ($stmt === false) {
            return;
        }

        $stmt->execute(["id" => $tenantId]);
    }
}
