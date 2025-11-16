<?php

declare(strict_types=1);

/**
 *
 * An application using the PHPolar Microframework
 * ==========================================================
 *
 * See `src/config/dependencies/conf.d/`.
 */

use EricFortmeyer\ActivityLog\UserInterface\Contexts\ServerErrorContext;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Phpolar\Phpolar\DependencyInjection\ContainerLoader;
use Phpolar\PurePhp\{
    HtmlSafeContext,
    TemplateEngine
};
use Psr\Log\LoggerInterface;

ini_set("display_errors", true);
chdir("../");

require "vendor/autoload.php";

/**
 *
 * Set up dependency injection
 * ==========================================================
 *
 * Use any PSR-11 container you like.
 * Just `composer require <the-container-implementation>`.
 * Then, return an instance of the PSR-11 container
 * implementation in the factory function below.
 */
$dependencyMap = new \Pimple\Container();
$psr11Container = new \Pimple\Psr11\Container($dependencyMap);

set_exception_handler(static function (Throwable $e) {
    $log = new Logger("Activity Log");
    $syslog = new SyslogHandler("Activity Log");
    $formatter = new LineFormatter("%channel%.%level_name%: %message% %context% %extra%");
    $syslog->setFormatter($formatter);
    $log->pushHandler($syslog);
    $log->alert("Exception", ["exception" => $e->getMessage(), "stacktrace" => $e->getTrace()]);

    http_response_code(500);
    echo new TemplateEngine()->apply(
        "500",
        new HtmlSafeContext(
            new ServerErrorContext(
                message: "An error occurred. We are investigating."
            )
        )
    );
});

new ContainerLoader()->load($psr11Container, $dependencyMap);

/**
 * @var \Closure
 */
$bootstrapper = $psr11Container->get("BOOTSTRAPPER");
/**
 * Start the application
 */
$bootstrapper();
