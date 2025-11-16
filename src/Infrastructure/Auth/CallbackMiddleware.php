<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Infrastructure\Auth;

use Auth0\SDK\Exception\StateException;
use EricFortmeyer\ActivityLog\AppConfig;
use Psr\Http\{
    Message\RequestInterface,
    Message\ServerRequestInterface,
    Message\ResponseInterface,
    Message\ResponseFactoryInterface,
    Server\RequestHandlerInterface
};
use Psr\Log\LoggerInterface;

final readonly class CallbackMiddleware extends AbstractRedirectMiddleware
{
    public function __construct(
        private Auth0Adapter $auth,
        private LoggerInterface $log,
        private AppConfig $appConfig,
        protected ResponseFactoryInterface $responseFactory,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUri()->getPath() !== $this->appConfig->callbackPath) {
            return $handler->handle($request);
        }

        try {
            $this->auth->exchange(
                $this->getCallbackUrl($request)
            );
            return $this->getRedirectResponse(
                $this->getReturnToUrl($request)
            );
        } catch (StateException $e) {
            $this->log->critical($e->getMessage());
            return $this->getRedirectResponse(
                $this->appConfig->logoutPath
            );
        }
    }

    private function getCallbackUrl(RequestInterface $request): string
    {
        return join(
            "",
            [
                $request->getUri()->getScheme(),
                "://",
                $request->getUri()->getHost(),
                $this->appConfig->callbackPath,
            ]
        );
    }

    private function getReturnToUrl(RequestInterface $request): string
    {
        return join(
            "",
            [
                $request->getUri()->getScheme(),
                "://",
                $request->getUri()->getHost()
            ]
        );
    }
}
