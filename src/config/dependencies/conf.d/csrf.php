<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use DateTimeImmutable;
use PhpContrib\Http\Message\{
    ResponseFilterInterface,
    ResponseFilterStrategyInterface
};
use Phpolar\CsrfProtection\{
    CsrfToken,
    Http\CsrfProtectionRequestHandler,
    Http\CsrfRequestCheckMiddleware,
    Http\CsrfResponseFilterMiddleware,
    Storage\AbstractTokenStorage,
    Storage\SessionTokenStorage,
    Storage\SessionWrapper
};
use Phpolar\CsrfResponseFilter\Http\Message\CsrfResponseFilter;
use Phpolar\CsrfResponseFilter\Http\Message\ResponseFilterPatternStrategy;
use Phpolar\Phpolar\DependencyInjection\DiTokens;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\{
    ResponseFactoryInterface,
    StreamFactoryInterface
};

use const Phpolar\CsrfProtection\{
    REQUEST_ID_KEY,
    TOKEN_DEFAULT_TTL,
    TOKEN_MAX
};

const CSRF_TOKEN_TTL = TOKEN_DEFAULT_TTL;
const REQUEST_ID = REQUEST_ID_KEY;
const MAX_STORED_TOKEN_COUNT = TOKEN_MAX;

/**
 * @phan-file-suppress PhanUnreferencedClosure
 */
return [
    "csrf_token" =>
    /**
     * Created on each request.
     *
     * Authentication will cause superfluous token creation.
     * Some dependency injection containers register dependencies
     * as singletons as long as a factory function is not used
     * to instantiate.  For that reason, we are not using a function
     * here.
     */
    new CsrfToken(
        new DateTimeImmutable("now"),
        CSRF_TOKEN_TTL,
    ),
    ResponseFilterStrategyInterface::class => static function (ContainerInterface $container) {
        /**
         * @var \Stringable $token
         */
        $token = $container->get("csrf_token");
        return new ResponseFilterPatternStrategy(
            $token,
            $container->get(StreamFactoryInterface::class),
            REQUEST_ID,
        );
    },
    ResponseFilterInterface::class => static fn(ContainerInterface $container) =>
    new CsrfResponseFilter($container->get(ResponseFilterStrategyInterface::class)),
    DiTokens::CSRF_CHECK_MIDDLEWARE => static fn(ContainerInterface $container) =>
    new CsrfRequestCheckMiddleware(
        $container->get(CsrfProtectionRequestHandler::class),
    ),
    DiTokens::CSRF_RESPONSE_FILTER_MIDDLEWARE => static fn(ContainerInterface $container) =>
    new CsrfResponseFilterMiddleware(
        $container->get(ResponseFilterInterface::class),
    ),
    CsrfProtectionRequestHandler::class => static fn(ContainerInterface $container) =>
    new CsrfProtectionRequestHandler(
        $container->get("csrf_token"),
        $container->get(AbstractTokenStorage::class),
        $container->get(ResponseFactoryInterface::class),
        REQUEST_ID,
    ),
    AbstractTokenStorage::class => static fn() =>
    new SessionTokenStorage(
        new SessionWrapper($_SESSION),
        REQUEST_ID,
        MAX_STORED_TOKEN_COUNT,
    ),
];
