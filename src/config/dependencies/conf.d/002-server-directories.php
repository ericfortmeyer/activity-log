<?php

declare(strict_types=1);

use const EricFortmeyer\ActivityLog\DI\Tokens\{
    APP_CONFIG_TABLE_NAME,
    DATA_DIR,
    SECRETS_DIR
};

return [
    APP_CONFIG_TABLE_NAME => getenv(APP_CONFIG_TABLE_NAME),
    DATA_DIR => getenv(DATA_DIR),
    SECRETS_DIR => getenv(SECRETS_DIR),
];
