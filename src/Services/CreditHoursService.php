<?php

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\CreditHours;
use Phpolar\Storage\{
    NotFound,
    StorageContext
};

readonly class CreditHoursService
{
    /**
     * @param StorageContext<CreditHours> $storageContext
     */
    public function __construct(private StorageContext $storageContext) {}

    public function save(CreditHours $creditHours, string $tenantId): void
    {
        if (empty($creditHours->id) === true) {
            $creditHours->create($tenantId);
            $this->storageContext->save($creditHours->id, $creditHours);
            return;
        }
        $this->storageContext->replace($creditHours->id, $creditHours);
    }

    public function get(string $id): CreditHours|NotFound
    {
        /**
         * @var CreditHours|NotFound $creditHours
         */
        $creditHours = $this->storageContext->find($id)
            ->orElse(static fn() => new NotFound())
            ->tryUnwrap();

        return $creditHours;
    }
}
