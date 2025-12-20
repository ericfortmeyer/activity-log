<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\DI;

use EricFortmeyer\ActivityLog\AppConfig;
use EricFortmeyer\ActivityLog\Bootstrapper;
use EricFortmeyer\ActivityLog\Clients\SecretsClient;
use EricFortmeyer\ActivityLog\EmailConfig;
use EricFortmeyer\ActivityLog\Infrastructure\Auth\Auth0Adapter;
use EricFortmeyer\ActivityLog\Infrastructure\Auth\AuthConfigService;
use EricFortmeyer\ActivityLog\RemarksForMonth;
use EricFortmeyer\ActivityLog\Services\ActivityLogDbConfigService;
use EricFortmeyer\ActivityLog\Services\AppConfigService;
use EricFortmeyer\ActivityLog\Services\CreditHoursService;
use EricFortmeyer\ActivityLog\Services\DataExportService;
use EricFortmeyer\ActivityLog\Services\RemarksForMonthService;
use EricFortmeyer\ActivityLog\Services\TenantService;
use EricFortmeyer\ActivityLog\Services\TimeEntryService;
use EricFortmeyer\ActivityLog\TimeEntry;
use EricFortmeyer\ActivityLog\Utils\Hasher;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Pdo\Mysql;
use Phpolar\HttpRequestProcessor\RequestProcessorInterface;
use Phpolar\Phpolar\ExceptionHandlerInterface;
use Phpolar\PurePhp\TemplateEngine;
use Phpolar\SqliteStorage\SqliteReadOnlyStorage;
use Phpolar\Storage\StorageContext;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;
use SQLite3;

use const EricFortmeyer\ActivityLog\DI\Tokens\APP_CONFIG_DB_CONNECTION;
use const EricFortmeyer\ActivityLog\DI\Tokens\APP_CONFIG_STORAGE;
use const EricFortmeyer\ActivityLog\DI\Tokens\APP_DB_CONNECTION;
use const EricFortmeyer\ActivityLog\DI\Tokens\BOOTSTRAPPER;
use const EricFortmeyer\ActivityLog\DI\Tokens\CALLBACK_MIDDLEWARE;
use const EricFortmeyer\ActivityLog\DI\Tokens\CREDIT_HOURS_STORAGE;
use const EricFortmeyer\ActivityLog\DI\Tokens\EXCEPTION_LOGGER;
use const EricFortmeyer\ActivityLog\DI\Tokens\LOGIN_MIDDLEWARE;
use const EricFortmeyer\ActivityLog\DI\Tokens\LOGOUT_MIDDLEWARE;
use const EricFortmeyer\ActivityLog\DI\Tokens\REMARKS_STORAGE as TokensREMARKS_STORAGE;
use const EricFortmeyer\ActivityLog\DI\Tokens\SECRETS_CLIENT;
use const EricFortmeyer\ActivityLog\DI\Tokens\TIME_ENTRY_STORAGE;

/**
 * @phan-file-suppress PhanUnreferencedUseConstant
 * @phan-file-suppress PhanReadOnlyPublicProperty
 * @codeCoverageIgnore
 */
final class ServiceProvider
{
    public AppConfig $appConfig {
        get {
            $appConfig = $this->container->get(AppConfig::class);
            return $appConfig instanceof AppConfig === false
                ? throw new MissingDependencyException(AppConfig::class)
                : $appConfig;
        }
    }

    public Bootstrapper $bootstrapper {
        get {
            $strapper = $this->container->get(BOOTSTRAPPER);
            return $strapper instanceof Bootstrapper === false
                ? throw new MissingDependencyException(BOOTSTRAPPER)
                : $strapper;
        }
    }

    public SQLite3 $appConfigConnection {
        get {
            $sQLite3Connection = $this->container->get(APP_CONFIG_DB_CONNECTION);
            return $sQLite3Connection instanceof SQLite3 === false
                ? throw new MissingDependencyException(APP_CONFIG_DB_CONNECTION)
                : $sQLite3Connection;
        }
    }

    public SqliteReadOnlyStorage $appConfigStorage {
        get {
            $appConfigStorage = $this->container->get(APP_CONFIG_STORAGE);
            return $appConfigStorage instanceof SqliteReadOnlyStorage === false
                ? throw new MissingDependencyException(APP_CONFIG_STORAGE)
                : $appConfigStorage;
        }
    }

    public AppConfigService $appConfigService {
        get {
            $appConfigService = $this->container->get(AppConfigService::class);
            return $appConfigService instanceof AppConfigService === false
                ? throw new MissingDependencyException(AppConfigService::class)
                : $appConfigService;
        }
    }

    public Mysql $appDataConnection {
        get {
            $appDataConnection = $this->container->get(APP_DB_CONNECTION);
            return $appDataConnection instanceof Mysql === false
                ? throw new MissingDependencyException(APP_DB_CONNECTION)
                : $appDataConnection;
        }
    }

    public Auth0Adapter $auth0Adapter {
        get {
            $auth0Adapter = $this->container->get(
                \PhpContrib\Authenticator\AuthenticatorInterface::class
            );
            return $auth0Adapter instanceof Auth0Adapter === false
                ? throw new MissingDependencyException(Auth0Adapter::class)
                : $auth0Adapter;
        }
    }

    public AuthConfigService $authConfigService {
        get {
            $authConfigService = $this->container->get(AuthConfigService::class);
            return $authConfigService instanceof AuthConfigService === false
                ? throw new MissingDependencyException(AuthConfigService::class)
                : $authConfigService;
        }
    }

    public ActivityLogDbConfigService $dbConfigService {
        get {
            $dbConfigService = $this->container->get(ActivityLogDbConfigService::class);
            return $dbConfigService instanceof ActivityLogDbConfigService === false
                ? throw new MissingDependencyException(ActivityLogDbConfigService::class)
                : $dbConfigService;
        }
    }

    public EmailConfig $emailConfig {
        get {
            $emailConfig = $this->container->get(EmailConfig::class);
            return $emailConfig instanceof EmailConfig === false
                ? throw new MissingDependencyException(EmailConfig::class)
                : $emailConfig;
        }
    }

    public Hasher $hasher {
        get {
            $hasher = $this->container->get(Hasher::class);
            return $hasher instanceof Hasher === false
                ? throw new MissingDependencyException(Hasher::class)
                : $hasher;
        }
    }

    public LoggerInterface $logger {
        get {
            $logger = $this->container->get(LoggerInterface::class);
            return $logger instanceof LoggerInterface === false
                ? throw new MissingDependencyException(LoggerInterface::class)
                : $logger;
        }
    }

    public LoggerInterface $exceptionLogger {
        get {
            $logger = $this->container->get(EXCEPTION_LOGGER);
            return $logger instanceof LoggerInterface === false
                ? throw new MissingDependencyException(EXCEPTION_LOGGER)
                : $logger;
        }
    }

    public TimeEntryService $timeEntryService {
        get {
            $timeEntryService = $this->container->get(TimeEntryService::class);
            return $timeEntryService instanceof TimeEntryService === false
                ? throw new MissingDependencyException(TimeEntryService::class)
                : $timeEntryService;
        }
    }

    public RemarksForMonthService $remarksForMonthService {
        get {
            $remarksForMonthService = $this->container->get(RemarksForMonthService::class);
            return $remarksForMonthService instanceof RemarksForMonthService === false
                ? throw new MissingDependencyException(RemarksForMonthService::class)
                : $remarksForMonthService;
        }
    }

    public CreditHoursService $creditHoursService {
        get {
            $creditHoursService = $this->container->get(CreditHoursService::class);
            return $creditHoursService instanceof CreditHoursService === false
                ? throw new MissingDependencyException(CreditHoursService::class)
                : $creditHoursService;
        }
    }

    public DataExportService $dataExportService {
        get {
            $dataExportService = $this->container->get(DataExportService::class);
            return $dataExportService instanceof DataExportService === false
                ? throw new MissingDependencyException(DataExportService::class)
                : $dataExportService;
        }
    }

    public TenantService $tenantService {
        get {
            $service = $this->container->get(TenantService::class);
            return $service instanceof TenantService === false
                ? throw new MissingDependencyException(TenantService::class)
                : $service;
        }
    }

    /**
     * Middleware =======================================================================
     */
    public MiddlewareInterface $callbackMiddleware {
        get {
            $callbackMiddleware = $this->container->get(CALLBACK_MIDDLEWARE);
            return $callbackMiddleware instanceof MiddlewareInterface === false
                ? throw new MissingDependencyException(CALLBACK_MIDDLEWARE)
                : $callbackMiddleware;
        }
    }

    public MiddlewareInterface $loginMiddleware {
        get {
            $loginMiddleware = $this->container->get(LOGIN_MIDDLEWARE);
            return $loginMiddleware instanceof MiddlewareInterface === false
                ? throw new MissingDependencyException(LOGIN_MIDDLEWARE)
                : $loginMiddleware;
        }
    }

    public MiddlewareInterface $logoutMiddleware {
        get {
            $logoutMiddleware = $this->container->get(LOGOUT_MIDDLEWARE);
            return $logoutMiddleware instanceof MiddlewareInterface === false
                ? throw new MissingDependencyException(LOGOUT_MIDDLEWARE)
                : $logoutMiddleware;
        }
    }
    // =======================================================================

    public ResponseFactoryInterface $responseFactory {
        get {
            $responseFactory = $this->container->get(ResponseFactoryInterface::class);
            return $responseFactory instanceof ResponseFactoryInterface === false
                ? throw new MissingDependencyException(ResponseFactoryInterface::class)
                : $responseFactory;
        }
    }

    public ExceptionHandlerInterface $exceptionHandler {
        get {
            $handler = $this->container->get(ExceptionHandlerInterface::class);
            return $handler instanceof ExceptionHandlerInterface === false
                ? throw new MissingDependencyException(ExceptionHandlerInterface::class)
                : $handler;
        }
    }

    public SecretsClient $secretsClient {
        get {
            $secretsClient = $this->container->get(SecretsClient::class);
            return $secretsClient instanceof SecretsClient === false
                ? throw new MissingDependencyException(SecretsClient::class)
                : $secretsClient;
        }
    }

    public ServerRequestCreatorInterface $serverRequestCreator {
        get {
            $serverRequestCreator = $this->container->get(ServerRequestCreatorInterface::class);
            return $serverRequestCreator instanceof ServerRequestCreatorInterface === false
                ? throw new MissingDependencyException(ServerRequestCreatorInterface::class)
                : $serverRequestCreator;
        }
    }

    public ClientInterface $secretsClientDep {
        get {
            $secretsClientDep = $this->container->get(SECRETS_CLIENT);
            return $secretsClientDep instanceof ClientInterface === false
                ? throw new MissingDependencyException(SECRETS_CLIENT)
                : $secretsClientDep;
        }
    }

    public ServerRequestInterface $serverRequest {
        get {
            $serverRequest = $this->container->get(ServerRequestInterface::class);
            return $serverRequest instanceof ServerRequestInterface === false
                ? throw new MissingDependencyException(ServerRequestInterface::class)
                : $serverRequest;
        }
    }

    public TemplateEngine $templateEngine {
        get {
            $templateEngine = $this->container->get(TemplateEngine::class);
            return $templateEngine instanceof TemplateEngine === false
                ? throw new MissingDependencyException(TemplateEngine::class)
                : $templateEngine;
        }
    }


    /**
     * STORAGE =============================================
     *
     */

    /**
     * @var StorageContext<\EricFortmeyer\ActivityLog\CreditHours>
     */
    public StorageContext $creditHoursStorage {
        get {
            $creditHoursStorage = $this->container->get(CREDIT_HOURS_STORAGE);
            return $creditHoursStorage instanceof StorageContext === false
                ? throw new MissingDependencyException(CREDIT_HOURS_STORAGE)
                : $creditHoursStorage;
        }
    }

    /**
     * @var StorageContext<RemarksForMonth>
     */
    public StorageContext $remarksStorage {
        get {
            $remarksStorage = $this->container->get(TokensREMARKS_STORAGE);
            return $remarksStorage instanceof StorageContext === false
                ? throw new MissingDependencyException(TokensREMARKS_STORAGE)
                : $remarksStorage;
        }
    }

    /**
     * @var StorageContext<TimeEntry>
     */
    public StorageContext $timeEntryStorage {
        get {
            $timeEntryStorage = $this->container->get(TIME_ENTRY_STORAGE);
            return $timeEntryStorage instanceof StorageContext === false
                ? throw new MissingDependencyException(TIME_ENTRY_STORAGE)
                : $timeEntryStorage;
        }
    }
    //=============================================


    /**
     * Request Processors =============================================
     */
    public RequestProcessorInterface $getTimeEntries {
        get {
            $getTimeEntries = $this->container->get(
                \EricFortmeyer\ActivityLog\Http\RequestProcessors\GetTimeEntries::class
            );
            return $getTimeEntries instanceof RequestProcessorInterface === false
                ? throw new MissingDependencyException(
                    \EricFortmeyer\ActivityLog\Http\RequestProcessors\GetTimeEntries::class
                )
                : $getTimeEntries;
        }
    }

    public RequestProcessorInterface $getTimeEntry {
        get {
            $getTimeEntry = $this->container->get(
                \EricFortmeyer\ActivityLog\Http\RequestProcessors\GetTimeEntry::class
            );
            return $getTimeEntry instanceof RequestProcessorInterface === false
                ? throw new MissingDependencyException(
                    \EricFortmeyer\ActivityLog\Http\RequestProcessors\GetTimeEntry::class
                )
                : $getTimeEntry;
        }
    }

    public RequestProcessorInterface $submitTimeEntry {
        get {
            $submitTimeEntry = $this->container->get(
                \EricFortmeyer\ActivityLog\Http\RequestProcessors\SubmitTimeEntry::class
            );
            return $submitTimeEntry instanceof RequestProcessorInterface === false
                ? throw new MissingDependencyException(
                    \EricFortmeyer\ActivityLog\Http\RequestProcessors\SubmitTimeEntry::class
                )
                : $submitTimeEntry;
        }
    }

    public RequestProcessorInterface $deleteTimeEntry {
        get {
            $deleteTimeEntry = $this->container->get(
                \EricFortmeyer\ActivityLog\Http\RequestProcessors\DeleteTimeEntry::class
            );
            return $deleteTimeEntry instanceof RequestProcessorInterface === false
                ? throw new MissingDependencyException(
                    \EricFortmeyer\ActivityLog\Http\RequestProcessors\DeleteTimeEntry::class
                )
                : $deleteTimeEntry;
        }
    }

    public RequestProcessorInterface $saveRemarksForMonth {
        get {
            $saveRemarksForMonth = $this->container->get(
                \EricFortmeyer\ActivityLog\Http\RequestProcessors\SaveRemarksForMonth::class
            );
            return $saveRemarksForMonth instanceof RequestProcessorInterface === false
                ? throw new MissingDependencyException(
                    \EricFortmeyer\ActivityLog\Http\RequestProcessors\SaveRemarksForMonth::class
                )
                : $saveRemarksForMonth;
        }
    }

    public RequestProcessorInterface $saveCreditHours {
        get {
            $saveCreditHours = $this->container->get(
                \EricFortmeyer\ActivityLog\Http\RequestProcessors\SaveCreditHours::class
            );
            return $saveCreditHours instanceof RequestProcessorInterface === false
                ? throw new MissingDependencyException(
                    \EricFortmeyer\ActivityLog\Http\RequestProcessors\SaveCreditHours::class
                )
                : $saveCreditHours;
        }
    }

    public RequestProcessorInterface $downloadDataExport {
        get {
            $downloadDataExport = $this->container->get(
                \EricFortmeyer\ActivityLog\Http\RequestProcessors\DownloadDataExport::class
            );
            return $downloadDataExport instanceof RequestProcessorInterface === false
                ? throw new MissingDependencyException(
                    \EricFortmeyer\ActivityLog\Http\RequestProcessors\DownloadDataExport::class
                )
                : $downloadDataExport;
        }
    }

    public RequestProcessorInterface $emailReportForMonth {
        get {
            $emailReportForMonth = $this->container->get(
                \EricFortmeyer\ActivityLog\Http\RequestProcessors\EmailReportForMonth::class
            );
            return $emailReportForMonth instanceof RequestProcessorInterface === false
                ? throw new MissingDependencyException(
                    \EricFortmeyer\ActivityLog\Http\RequestProcessors\EmailReportForMonth::class
                )
                : $emailReportForMonth;
        }
    }
    //=============================================

    public function __construct(
        /**
         * @suppress PhanWriteOnlyPrivateProperty
         */
        private readonly ContainerInterface $container,
    ) {}
}
