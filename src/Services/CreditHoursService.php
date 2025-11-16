<?php

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\CreditHours;
use Phpolar\Phpolar\Auth\User;
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

    public function save(CreditHours $creditHours, User $user): void
    {
        if (empty($creditHours->id)) {
            $creditHours->create($user);
            $this->storageContext->save($creditHours->id, $creditHours);
            return;
        }
        $this->storageContext->replace($creditHours->id, $creditHours);
    }

    public function get(string $id): CreditHours|NotFound
    {
        return $this->storageContext->find($id)
            ->orElse(static fn() => new NotFound())
            ->tryUnwrap();
    }
}
