<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http;

use EricFortmeyer\ActivityLog\AppReleaseAction;
use EricFortmeyer\ActivityLog\AppReleaseEvent;
use EricFortmeyer\ActivityLog\Services\AppConfigService;
use EricFortmeyer\ActivityLog\Utils\Hasher;
use PhpCommonEnums\HttpResponseCode\Enumeration\HttpResponseCodeEnum;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class NotifyReleaseEventMiddleware implements MiddlewareInterface
{
    private const EVENT_TYPE_HEADER_KEY = "X-GitHub-Event";
    private const HOOK_ID_HEADER_KEY = "X-GitHub-Hook-ID";
    private const SIGNATURE_HEADER_KEY = "X-Hub-Signature-256";
    private const RELEASE_EVENT_TYPE = "release";

    public function __construct(
        private string $releaseEventDestination,
        private string $releaseEventHookPath,
        private bool $releaseEventHookRetryEnabled,
        private ResponseFactoryInterface $responseFactory,
        private AppConfigService $appVersionUpdater,
        private Hasher $verifier,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestPath = $request->getUri()->getPath();

        if (in_array($requestPath, [$this->releaseEventHookPath]) === false) {
            return $handler->handle($request);
        }

        $requestBody = $request->getBody()->getContents();
        $eventType = $request->getHeader(self::EVENT_TYPE_HEADER_KEY)[0] ?? "ignore";
        $hookId = $request->getHeader(self::HOOK_ID_HEADER_KEY)[0] ?? "ignore";
        $signature = ltrim($request->getHeader(self::SIGNATURE_HEADER_KEY)[0] ?? "ignore", "sha256=");

        if ($this->verifier->verify($requestBody, $signature) === false) {
            return $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::Unauthorized->value,
                HttpResponseCodeEnum::Unauthorized->name
            );
        }

        $jsonDecodeResult = json_decode($requestBody);
        $releaseEvent = new AppReleaseEvent(is_object($jsonDecodeResult) ? $jsonDecodeResult : []);

        return match (false) {
            // do not process
            // missing required header
            $eventType === self::RELEASE_EVENT_TYPE => $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::NotImplemented->value,
                HttpResponseCodeEnum::NotImplemented->name
            ),
            // validate
            is_numeric($hookId) => $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::BadRequest->value,
                HttpResponseCodeEnum::BadRequest->name
            ),
            $this->releaseEventHookRetryEnabled && apcu_exists($hookId) => $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::TooManyRequests->value,
                HttpResponseCodeEnum::TooManyRequests->name
            ),
            $releaseEvent->isValid() => $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::BadRequest->value,
                HttpResponseCodeEnum::BadRequest->name
            ),
            // only handle created releases
            $releaseEvent->action === AppReleaseAction::Created => $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::Accepted->value,
                HttpResponseCodeEnum::Accepted->name
            ),
            // event save error?
            apcu_add($hookId, $hookId, 0) === true
                && file_put_contents(
                    sprintf(
                        "%s/%d.json",
                        $this->releaseEventDestination,
                        $hookId,
                    ),
                    $requestBody,
                ) !== false
                && $this->appVersionUpdater->updateVersion($releaseEvent->release->tagName) =>
            $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::InternalServerError->value,
                HttpResponseCodeEnum::InternalServerError->name
            ),
            // event save succesful
            default => $this->responseFactory->createResponse(
                (int) HttpResponseCodeEnum::Accepted->value,
                HttpResponseCodeEnum::Accepted->name
            ),
        };
    }
}
