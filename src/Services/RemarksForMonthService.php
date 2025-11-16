<?php

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\RemarksForMonth;
use Phpolar\Phpolar\Auth\User;
use Phpolar\Storage\{
    NotFound,
    StorageContext
};

readonly class RemarksForMonthService
{
    /**
     * @param StorageContext<RemarksForMonth> $storageContext
     */
    public function __construct(private StorageContext $storageContext) {}

    public function save(RemarksForMonth $remarks, User $user): void
    {
        if (empty($remarks->id)) {
            $remarks->create($user);
            $this->storageContext->save($remarks->id, $remarks);
            return;
        }
        $this->storageContext->replace($remarks->id, $remarks);
    }

    public function get(string $id): RemarksForMonth|NotFound
    {
        return $this->storageContext->find($id)
            ->orElse(static fn() => new NotFound())
            ->tryUnwrap();
    }
}
