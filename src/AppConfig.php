<?php

namespace EricFortmeyer\ActivityLog;

final readonly class AppConfig
{
    public function __construct(
        public string $appName,
        public string $callbackPath,
        public string $loginPath,
        public string $logoutPath,

    ) {}
}
