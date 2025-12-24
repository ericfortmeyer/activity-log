<?php
/**
 * This is an automatically generated baseline for Phan issues.
 * When Phan is invoked with --load-baseline=path/to/baseline.php,
 * The pre-existing issues listed in this file won't be emitted.
 *
 * This file can be updated by invoking Phan with --save-baseline=path/to/baseline.php
 * (can be combined with --load-baseline)
 */
return [
    // # Issue statistics:
    // PhanReadOnlyPublicProperty : 65+ occurrences
    // PhanUnreferencedClosure : 35+ occurrences
    // PhanUnreferencedConstant : 30+ occurrences
    // PhanUnreferencedUseConstant : 30+ occurrences
    // PhanUnreferencedPublicProperty : 5 occurrences
    // PhanCoalescingNeverNull : 2 occurrences
    // PhanUnreferencedPublicMethod : 2 occurrences
    // PhanWriteOnlyPublicProperty : 2 occurrences
    // PhanParamTooFewUnpack : 1 occurrence
    // PhanPartialTypeMismatchReturn : 1 occurrence
    // PhanUnreferencedClass : 1 occurrence
    // PhanUnreferencedPrivateProperty : 1 occurrence

    'file_suppressions' => [
        'src/AppConfig.php' => [
            'PhanReadOnlyPublicProperty' => ['\\EricFortmeyer\\ActivityLog\\AppConfig'],
            'PhanUnreferencedPublicProperty' => ['\\EricFortmeyer\\ActivityLog\\AppConfig']
        ],
        'src/CreditHours.php' => [
            'PhanReadOnlyPublicProperty' => ['\\EricFortmeyer\\ActivityLog\\CreditHours']
        ],
        'src/DI/MissingDependencyException.php' => [
            'PhanUnreferencedClass' => ['src/DI/MissingDependencyException.php']
        ],
        'src/DI/ServiceProvider.php' => [
            'PhanReadOnlyPublicProperty' => ['\\EricFortmeyer\\ActivityLog\\DI\\ServiceProvider'],
            'PhanUnreferencedPublicProperty' => ['\\EricFortmeyer\\ActivityLog\\DI\\ServiceProvider'],
            'PhanUnreferencedUseConstant' => ['src/DI/ServiceProvider.php']
        ],
        'src/DI/Tokens.php' => [
            'PhanUnreferencedConstant' => ['src/DI/Tokens.php']
        ],
        'src/DI/ValueProvider.php' => [
            'PhanReadOnlyPublicProperty' => ['\\EricFortmeyer\\ActivityLog\\DI\\ValueProvider'],
            'PhanUnreferencedPublicProperty' => ['\\EricFortmeyer\\ActivityLog\\DI\\ValueProvider'],
            'PhanUnreferencedUseConstant' => ['src/DI/ValueProvider.php']
        ],
        'src/EmailReport.php' => [
            'PhanReadOnlyPublicProperty' => ['\\EricFortmeyer\\ActivityLog\\EmailReport']
        ],
        'src/MonthFilters.php' => [
            'PhanReadOnlyPublicProperty' => ['\\EricFortmeyer\\ActivityLog\\MonthFilters']
        ],
        'src/RemarksForMonth.php' => [
            'PhanReadOnlyPublicProperty' => ['\\EricFortmeyer\\ActivityLog\\RemarksForMonth']
        ],
        'src/Services/DataExportService.php' => [
            'PhanPartialTypeMismatchReturn' => ['\\EricFortmeyer\\ActivityLog\\Services\\DataExportService::convertToString']
        ],
        'src/Services/TenantService.php' => [
            'PhanUnreferencedPublicMethod' => ['\\EricFortmeyer\\ActivityLog\\Services\\TenantService::getAll']
        ],
        'src/Tenant.php' => [
            'PhanUnreferencedPublicProperty' => ['\\EricFortmeyer\\ActivityLog\\Tenant']
        ],
        'src/TimeEntry.php' => [
            'PhanReadOnlyPublicProperty' => ['\\EricFortmeyer\\ActivityLog\\TimeEntry'],
            'PhanUnreferencedPrivateProperty' => ['\\EricFortmeyer\\ActivityLog\\TimeEntry']
        ],
        'src/UserInterface/Contexts/EmailReportContext.php' => [
            'PhanUnreferencedPublicMethod' => ['\\EricFortmeyer\\ActivityLog\\UserInterface\\Contexts\\EmailReportContext::getMonthTitle'],
            'PhanWriteOnlyPublicProperty' => ['\\EricFortmeyer\\ActivityLog\\UserInterface\\Contexts\\EmailReportContext']
        ],
        'src/UserInterface/Contexts/TimeEntriesContext.php' => [
            'PhanCoalescingNeverNull' => ['\\EricFortmeyer\\ActivityLog\\UserInterface\\Contexts\\TimeEntriesContext::getRemarksMonth', '\\EricFortmeyer\\ActivityLog\\UserInterface\\Contexts\\TimeEntriesContext::getRemarksYear']
        ],
        'src/config/dependencies/conf.d/000-bootstrap.php' => [
            'PhanUnreferencedClosure' => ['\\closure']
        ],
        'src/config/dependencies/conf.d/001-app.php' => [
            'PhanUnreferencedClosure' => ['\\closure']
        ],
        'src/config/dependencies/conf.d/001-logging.php' => [
            'PhanUnreferencedClosure' => ['\\closure']
        ],
        'src/config/dependencies/conf.d/auth.php' => [
            'PhanUnreferencedClosure' => ['\\closure']
        ],
        'src/config/dependencies/conf.d/clients.php' => [
            'PhanUnreferencedClosure' => ['\\closure']
        ],
        'src/config/dependencies/conf.d/errors.php' => [
            'PhanUnreferencedClosure' => ['\\closure']
        ],
        'src/config/dependencies/conf.d/model-resolver.php' => [
            'PhanUnreferencedClosure' => ['\\closure']
        ],
        'src/config/dependencies/conf.d/property-injector.php' => [
            'PhanUnreferencedClosure' => ['\\closure']
        ],
        'src/config/dependencies/conf.d/request-processors.php' => [
            'PhanUnreferencedClosure' => ['\\closure']
        ],
        'src/config/dependencies/conf.d/server-request.php' => [
            'PhanParamTooFewUnpack' => ['\\closure'],
            'PhanUnreferencedClosure' => ['\\closure']
        ],
        'src/config/dependencies/conf.d/server.php' => [
            'PhanUnreferencedClosure' => ['\\closure']
        ],
        'src/config/dependencies/conf.d/services.php' => [
            'PhanUnreferencedClosure' => ['\\closure']
        ],
        'src/config/dependencies/conf.d/storage.php' => [
            'PhanUnreferencedClosure' => ['\\closure']
        ],
        'src/config/dependencies/conf.d/utils.php' => [
            'PhanUnreferencedClosure' => ['\\closure']
        ],
    ],
    // 'directory_suppressions' => ['src/directory_name' => ['PhanIssueName1', 'PhanIssueName2']] can be manually added if needed.
    // (directory_suppressions will currently be ignored by subsequent calls to --save-baseline, but may be preserved in future Phan releases)
];
