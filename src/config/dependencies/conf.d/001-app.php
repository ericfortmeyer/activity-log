<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use PDO;
use Psr\Container\ContainerInterface;

use const EricFortmeyer\ActivityLog\config\DiTokens\{
    APP_CALLBACK_PATH,
    APP_NAME,
    APP_DB,
    APP_LOGIN_PATH,
    APP_LOGOUT_PATH,
};
use const EricFortmeyer\ActivityLog\config\FileNames\APP_DB_STORAGE;

return [
    AppConfig::class => static fn(ContainerInterface $container) => new AppConfig(
        appName: $container->get(APP_NAME),
        callbackPath: $container->get(APP_CALLBACK_PATH),
        loginPath: $container->get(APP_LOGIN_PATH),
        logoutPath: $container->get(APP_LOGOUT_PATH),
    ),
    APP_DB => static fn(ContainerInterface $container) => new PDO(sprintf(
        "sqlite:%s/%s",
        $container->get("DATA_DIR"),
        APP_DB_STORAGE
    )),
    APP_NAME => static function (ContainerInterface $container): string {
        $appName = "";
        if (\apcu_exists("app_name") === true) {
            $fetched = \apcu_fetch("app_name");
            if ($fetched !== false) {
                return $fetched;
            }
        }
        /**
         * @var PDO $db
         */
        $db = $container->get(APP_DB);
        $sth = $db->prepare("SELECT app_name FROM activity_log_config");
        if ($sth === false) {
            return "";
        }

        $sth->bindColumn("app_name", $appName);
        $sth->execute();
        $sth->fetchAll();
        \apcu_store("app_name", $appName);
        return $appName;
    },
    APP_LOGOUT_PATH => static function (ContainerInterface $container): string {
        $logoutPath = "";
        if (\apcu_exists("app_logout_path") === true) {
            $fetched = \apcu_fetch("app_logout_path");
            if ($fetched !== false) {
                return $fetched;
            }
        }
        /**
         * @var PDO $db
         */
        $db = $container->get(APP_DB);
        $sth = $db->prepare("SELECT app_logout_path FROM activity_log_config");
        if ($sth === false) {
            return "";
        }

        $sth->bindColumn("app_logout_path", $logoutPath);
        $sth->execute();
        $sth->fetchAll();
        \apcu_store("app_logout_path", $logoutPath);
        return $logoutPath;
    },
    APP_LOGIN_PATH => static function (ContainerInterface $container): string {
        $loginPath = "";
        if (\apcu_exists("app_login_path") === true) {
            $fetched = \apcu_fetch("app_login_path");
            if ($fetched !== false) {
                return $fetched;
            }
        }
        /**
         * @var PDO $db
         */
        $db = $container->get(APP_DB);
        $sth = $db->prepare("SELECT app_login_path FROM activity_log_config");
        if ($sth === false) {
            return "";
        }

        $sth->bindColumn("app_login_path", $loginPath);
        $sth->execute();
        $sth->fetchAll();
        \apcu_store("app_login_path", $loginPath);
        return $loginPath;
    },
    APP_CALLBACK_PATH => static function (ContainerInterface $container): string {
        $callbackPath = "";
        if (\apcu_exists("app_callback_path") === true) {
            $fetched = \apcu_fetch("app_callback_path");
            if ($fetched !== false) {
                return $fetched;
            }
        }
        /**
         * @var PDO $db
         */
        $db = $container->get(APP_DB);
        $sth = $db->prepare("SELECT app_callback_path FROM activity_log_config");
        if ($sth === false) {
            return "";
        }

        $sth->bindColumn("app_callback_path", $callbackPath);
        $sth->execute();
        $sth->fetchAll();
        \apcu_store("app_callback_path", $callbackPath);
        return $callbackPath;
    },
];
