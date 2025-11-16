<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

final readonly class EmailConfig
{
    /**
     * @param array<string,string> $headers
     */
    public function __construct(public array $headers) {}
}
