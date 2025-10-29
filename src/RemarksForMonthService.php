<?php

namespace EricFortmeyer\ActivityLog;

use Phpolar\Storage\NotFound;
use Phpolar\Storage\StorageContext;

class RemarksForMonthService
{
    public function __construct(private readonly StorageContext $storageContext)
    {
    }

    public function save(RemarksForMonth $remarks): void
    {
        if (empty($remarks->id)) {
            $remarks->create($remarks->year, $remarks->month);
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
