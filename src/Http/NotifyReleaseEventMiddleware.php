<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http;

use EricFortmeyer\ActivityLog\AppReleaseEvent;
use EricFortmeyer\ActivityLog\Services\AppConfigService;
use EricFortmeyer\ActivityLog\Utils\Hasher;
use PhpCommonEnums\HttpMethod\Enumeration\HttpMethodEnum;
use PhpCommonEnums\HttpResponseCode\Enumeration\HttpResponseCodeEnum;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class NotifyReleaseEventMiddleware implements MiddlewareInterface
{
    private const SIGNATURE_HEADER_KEY = "X-Hub-Signature-256";
    private const SUPPORTED_METHODS = [HttpMethodEnum::Post];

    public function __construct(
        private string $releaseEventDestination,
        private string $releaseEventHookPath,
        private ResponseFactoryInterface $responseFactory,
        private AppConfigService $appVersionUpdater,
        private Hasher $verifier,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestBody = $request->getBody()->getContents();
        return match (false) {
            in_array($request->getUri()->getPath(), [$this->releaseEventHookPath]) => $handler->handle($request),
            in_array(HttpMethodEnum::from($request->getMethod()), self::SUPPORTED_METHODS) =>
            $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::MethodNotAllowed->value,
                HttpResponseCodeEnum::MethodNotAllowed->name
            ),
            str_starts_with(
                $request->getHeader("User-Agent")[0] ?? "invalid!!!",
                "GitHub-Hookshot",
            ) => $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::Unauthorized->value,
                HttpResponseCodeEnum::Unauthorized->name
            ),
            $this->verifier->verify(
                data: $requestBody,
                signature: ltrim($request->getHeader(self::SIGNATURE_HEADER_KEY)[0] ?? "ignore", "sha256="),
            ) => $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::Unauthorized->value,
                HttpResponseCodeEnum::Unauthorized->name
            ),
            AppReleaseEvent::isReleaseEventRequest($request) =>
            $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::NotImplemented->value,
                HttpResponseCodeEnum::NotImplemented->name
            ),
            AppReleaseEvent::fromRequest($requestBody, $request)->isValid() =>
            $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::BadRequest->value,
                HttpResponseCodeEnum::BadRequest->name
            ),
            $this->handleReleaseEvent(
                AppReleaseEvent::fromRequest($requestBody, $request),
                $requestBody,
            ) => $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::InternalServerError->value,
                HttpResponseCodeEnum::InternalServerError->name
            ),
            default => $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::Accepted->value,
                HttpResponseCodeEnum::Accepted->name
            ),
        };
    }

    private function handleReleaseEvent(
        AppReleaseEvent $event,
        string $requestBody,
    ): bool {
        return file_put_contents(
            sprintf(
                "%s/%d.json",
                $this->releaseEventDestination,
                $event->release->id,
            ),
            $requestBody,
        ) !== false
            && $this->appVersionUpdater->updateVersion($event->release->tagName);
    }
}
