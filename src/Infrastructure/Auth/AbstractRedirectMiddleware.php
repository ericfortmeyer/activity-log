<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Infrastructure\Auth;

use PhpCommonEnums\HttpResponseCode\Enumeration\HttpResponseCodeEnum;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

abstract readonly class AbstractRedirectMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
    ) {}

    protected function getRedirectResponse(string $location): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(
                HttpResponseCodeEnum::TemporaryRedirect->value,
                HttpResponseCodeEnum::TemporaryRedirect->getLabel()
            )->withHeader("Location", $location);
    }
}
