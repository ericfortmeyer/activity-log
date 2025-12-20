<?php

declare(strict_types=1);

/**
 * ==========================================================
 * The starting point for execution of the
 * Activity Log application
 *
 * This application uses the PHPolar Microframework
 * ==========================================================
 */

chdir("../");

require "vendor/autoload.php";

/**
 *
 * Set up dependency injection
 * ==========================================================
 *
 * See `src/config/dependencies/conf.d/`.
 * Use any PSR-11 container you like.
 * Just `composer require <the-container-implementation>`.
 * Then, return an instance of the PSR-11 container
 * implementation in the factory function below.
 */
$dependencyMap = new \Pimple\Container();
$container = new \Pimple\Psr11\Container($dependencyMap);

/**
 * Wire up dependency graph
 */
new Phpolar\Phpolar\DependencyInjection\ContainerLoader()->load($container, $dependencyMap);

/**
 * Start the application
 */
$bootstrapper = new EricFortmeyer\ActivityLog\DI\ServiceProvider($container)->bootstrapper;
$bootstrapper();
