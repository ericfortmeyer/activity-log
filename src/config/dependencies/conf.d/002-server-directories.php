<?php

declare(strict_types=1);

use const EricFortmeyer\ActivityLog\config\DiTokens\{
    DATA_DIR,
    SECRETS_DIR
};

return [
    DATA_DIR => getenv(DATA_DIR),
    SECRETS_DIR => getenv(SECRETS_DIR),
];
