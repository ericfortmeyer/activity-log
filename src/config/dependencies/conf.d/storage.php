<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\CsvFileStorage\CsvFileStorage;
use Psr\Container\ContainerInterface;

use const EricFortmeyer\ActivityLog\DiTokens\CREDIT_HOURS_CSV_FILE;
use const EricFortmeyer\ActivityLog\DiTokens\CREDIT_HOURS_STORAGE as DiTokensCREDIT_HOURS_STORAGE;
use const EricFortmeyer\ActivityLog\DiTokens\REMARKS_CSV_FILE;
use const EricFortmeyer\ActivityLog\DiTokens\REMARKS_STORAGE as DiTokensREMARKS_STORAGE;
use const EricFortmeyer\ActivityLog\DiTokens\TIME_ENTRY_CSV_FILE;
use const EricFortmeyer\ActivityLog\DiTokens\TIME_ENTRY_STORAGE as DiTokensTIME_ENTRY_STORAGE;
use const EricFortmeyer\ActivityLog\FileNames\CREDIT_HOURS_STORAGE;
use const EricFortmeyer\ActivityLog\FileNames\REMARKS_STORAGE;
use const EricFortmeyer\ActivityLog\FileNames\TIME_ENTRY_STORAGE;

return [
    TIME_ENTRY_CSV_FILE => join(DIRECTORY_SEPARATOR, ["/srv/www/data", TIME_ENTRY_STORAGE]),
    REMARKS_CSV_FILE => join(DIRECTORY_SEPARATOR, ["/srv/www/data", REMARKS_STORAGE]),
    CREDIT_HOURS_CSV_FILE => join(DIRECTORY_SEPARATOR, ["/srv/www/data", CREDIT_HOURS_STORAGE]),
    DiTokensCREDIT_HOURS_STORAGE => static fn(ContainerInterface $container) => new CsvFileStorage(
        filename: $container->get(CREDIT_HOURS_CSV_FILE),
        typeClassName: CreditHours::class,
    ),
    DiTokensTIME_ENTRY_STORAGE => static fn(ContainerInterface $container) => new CsvFileStorage(
        filename: $container->get(TIME_ENTRY_CSV_FILE),
        typeClassName: TimeEntry::class,
    ),
    DiTokensREMARKS_STORAGE => static fn(ContainerInterface $container) => new CsvFileStorage(
        filename: $container->get(REMARKS_CSV_FILE),
        typeClassName: RemarksForMonth::class,
    ),
];
