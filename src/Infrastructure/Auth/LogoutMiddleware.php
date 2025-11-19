<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Infrastructure\Auth;

use Auth0\SDK\Exception\ConfigurationException;
use EricFortmeyer\ActivityLog\AppConfig;
use EricFortmeyer\ActivityLog\Http\AbstractRedirectMiddleware;
use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface,
    Server\RequestHandlerInterface
};
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;

final readonly class LogoutMiddleware extends AbstractRedirectMiddleware
{
    public function __construct(
        private Auth0Adapter $auth,
        private LoggerInterface $log,
        private AppConfig $appConfig,
        protected ResponseFactoryInterface $responseFactory,
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if ($request->getUri()->getPath() !== $this->appConfig->logoutPath) {
            return $handler->handle($request);
        }

        $this->auth->clear();

        try {
            $logoutUrl = $this->auth->logout(
                $request->getUri()->getScheme() . "://" . $request->getUri()->getHost()
            );
            return $this->getRedirectResponse($logoutUrl);
        } catch (ConfigurationException $e) {
            $this->log->critical($e->getMessage());
            return $this->getRedirectResponse(
                $this->appConfig->loginPath
            );
        }
    }
}
