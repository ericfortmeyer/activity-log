<?php

declare(strict_types=1);

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Pdo\Mysql;

const ACTIVITY_LOG_APP_PASSWD_KEY = "activity_log_app_db_password";
const APP_DB = "activity_log";
const DB_HOST = "127.0.0.1";
const DB_PORT = "3306";
const MIGRATOR_USER = "activity_log_migrator";
const SECRETS_USER = "activity-log-migrator";
const SECRETS_APP_KEY = "activity-log";
const SECRETS_APP_PATH = "kv/data";
const CERTS_DIR = "/srv/www/certs";
const SSL_KEY = CERTS_DIR . DIRECTORY_SEPARATOR . "priv_key.pem";
const SSL_CERT = CERTS_DIR . DIRECTORY_SEPARATOR . "cert.pem";
const SECRETS_LOGIN_KEY = "activity-log-application";
const SECRETS_LOGIN_PATH = "auth/cert/login";
const SECRETS_SERVICE_HOST = "https://activity-log.phpolar.org";
const SECRETS_SERVICE_PORT = "8200";


if (getenv("ENVIRONMENT") === "DEVELOPMENT") {
    return new PDO("sqlite::memory");
}

// Login
$json = json_encode([
    "name" => SECRETS_USER,
]);

if ($json === false) {
    exit(127);
}

$client = new HttpClient(
    [
        "base_uri" => sprintf(
            "%s:%s/v1/",
            SECRETS_SERVICE_HOST,
            SECRETS_SERVICE_PORT,
        ),
        RequestOptions::SSL_KEY => SSL_KEY,
        RequestOptions::CERT => SSL_CERT
    ]
);

$loginResponse = $client->sendRequest(
    new Request(
        method: "POST",
        uri: SECRETS_LOGIN_PATH,
        body: $json,
    )
);

if ($loginResponse->getStatusCode() !== 200) {
    echo $loginResponse->getReasonPhrase() . PHP_EOL;
    echo $loginResponse->getBody()->getContents() . PHP_EOL;
    exit(127);
}

$loginResponseContents = json_decode($loginResponse->getBody()->getContents(), true);

if (\is_array($loginResponseContents) === false) {
    exit(127);
}

if (array_key_exists("errors", $loginResponseContents) === true) {
    print_r($loginResponseContents["errors"]);
    exit(127);
}

if (\array_key_exists("auth", $loginResponseContents) === false) {
    print_r($loginResponseContents);
    exit(127);
}

["auth" => $loginAuth] = $loginResponseContents;

if (is_array($loginAuth) === false) {
    print_r($loginAuth);
    exit(127);
}

if (\array_key_exists("client_token", $loginAuth) === false) {
    print_r($loginAuth);
    exit(127);
}

["client_token" => $authClientToken] = $loginAuth;

if (is_string($authClientToken) === false) {
    print_r($loginAuth);
    exit(127);
}

$dbPasswordResponse = $client->sendRequest(
    new Request(
        method: "GET",
        uri: join(DIRECTORY_SEPARATOR, [SECRETS_APP_PATH, SECRETS_USER]),
        headers: ["X-Vault-Token" => $authClientToken],
    )
);

if ($dbPasswordResponse->getStatusCode() !== 200) {
    echo $dbPasswordResponse->getReasonPhrase() . PHP_EOL;
    echo $dbPasswordResponse->getBody()->getContents() . PHP_EOL;
    exit(127);
}

$dbPasswordResponseContents = json_decode($dbPasswordResponse->getBody()->getContents(), true);

if (\is_array($dbPasswordResponseContents) === false) {
    exit(127);
}

if (array_key_exists("errors", $dbPasswordResponseContents)) {
    print_r($dbPasswordResponseContents);
    exit(127);
}

if (\array_key_exists("data", $dbPasswordResponseContents) === false) {
    print_r($dbPasswordResponseContents);
    exit(127);
}

["data" => $dbPasswordResponseData] = $dbPasswordResponseContents;

if (is_array($dbPasswordResponseData) === false) {
    print_r($dbPasswordResponseData);
    exit(127);
}

if (\array_key_exists("data", $dbPasswordResponseData) === false) {
    print_r($dbPasswordResponseData);
    exit(127);
}

["data" => $dbPasswordResponseDataData] = $dbPasswordResponseData;

if (is_array($dbPasswordResponseDataData) === false) {
    print_r($dbPasswordResponseDataData);
    exit(127);
}

if (array_key_exists(MIGRATOR_USER, $dbPasswordResponseDataData) === false) {
    print_r($dbPasswordResponseDataData);
    exit(127);
}

[MIGRATOR_USER => $password] = $dbPasswordResponseDataData;

if (is_string($password) === false) {
    print_r($password);
    exit(127);
}

return new Mysql(
    dsn: sprintf(
        "mysql:host=%s:%s;dbname=%s",
        DB_HOST,
        DB_PORT,
        APP_DB,
    ),
    username: MIGRATOR_USER,
    password: $password,
);
