<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use EricFortmeyer\ActivityLog\DI\ValueProvider;
use EricFortmeyer\ActivityLog\EmailConfig;

return [
    EmailConfig::class => new EmailConfig(
        headers: [
            "MIME-Version" => "1.0",
            "Content-Type" => "text/html; charset=iso-8859-1",
            "From" => new ValueProvider()->fromAddress,
        ],
    ),
];
