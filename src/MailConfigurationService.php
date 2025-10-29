<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

class MailConfigurationService
{
    public function __construct(private readonly array $headers)
    {
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
