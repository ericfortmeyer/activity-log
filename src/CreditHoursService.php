<?php

namespace EricFortmeyer\ActivityLog;

use Phpolar\Storage\NotFound;
use Phpolar\Storage\StorageContext;

class CreditHoursService
{
    public function __construct(private readonly StorageContext $storageContext)
    {
    }

    public function save(CreditHours $creditHours): void
    {
        if (empty($creditHours->id)) {
            $creditHours->create($creditHours->year, $creditHours->month);
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
