<?php

declare(strict_types=1);

use EricFortmeyer\ActivityLog\{
    CreditHours,
    RemarksForMonth,
    TimeEntry
};
use Phpolar\CsvFileStorage\CsvFileStorage;
use Psr\Container\ContainerInterface;

use const EricFortmeyer\ActivityLog\config\DiTokens\{
    APP_DB_FILE,
    CREDIT_HOURS_CSV_FILE,
    CREDIT_HOURS_STORAGE as DiTokensCREDIT_HOURS_STORAGE,
    LOGIN_PASSWD_FILE,
    REMARKS_CSV_FILE,
    REMARKS_STORAGE as DiTokensREMARKS_STORAGE,
    TIME_ENTRY_CSV_FILE,
    TIME_ENTRY_STORAGE as DiTokensTIME_ENTRY_STORAGE,
};
use const EricFortmeyer\ActivityLog\config\FileNames\{
    CREDIT_HOURS_STORAGE,
    REMARKS_STORAGE,
    TIME_ENTRY_STORAGE,
    LOGIN_PASSWD,
    APP_DB_STORAGE
};

return [
    LOGIN_PASSWD_FILE => static fn(ContainerInterface $container) => join(DIRECTORY_SEPARATOR, [
        $container->get("SECRETS_DIR"),
        LOGIN_PASSWD
    ]),
    TIME_ENTRY_CSV_FILE => static fn(ContainerInterface $container) => join(DIRECTORY_SEPARATOR, [
        $container->get("DATA_DIR"),
        TIME_ENTRY_STORAGE,
    ]),
    REMARKS_CSV_FILE => static fn(ContainerInterface $container) => join(DIRECTORY_SEPARATOR, [
        $container->get("DATA_DIR"),
        REMARKS_STORAGE,
    ]),
    CREDIT_HOURS_CSV_FILE => static fn(ContainerInterface $container) => join(DIRECTORY_SEPARATOR, [
        $container->get("DATA_DIR"),
        CREDIT_HOURS_STORAGE,
    ]),
    APP_DB_FILE => static fn(ContainerInterface $container) => join(DIRECTORY_SEPARATOR, [
        $container->get("DATA_DIR"),
        APP_DB_STORAGE
    ]),
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
