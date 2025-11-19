<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http;

use PhpCommonEnums\HttpResponseCode\Enumeration\HttpResponseCodeEnum as ResponseCode;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class UnauthorizedHandler implements RequestHandlerInterface
{
    public function __construct(
        private string $loginPath,
        private ResponseFactoryInterface $responseFactory
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory->createResponse(
            ResponseCode::TemporaryRedirect->value,
            ResponseCode::TemporaryRedirect->getLabel(),
        )->withHeader("Location", $this->loginPath);
    }
}
