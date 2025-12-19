<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UnitTests\Infrastructure\Auth;

use Auth0\SDK\Exception\StateException;
use EricFortmeyer\ActivityLog\AppConfig;
use EricFortmeyer\ActivityLog\Http\AbstractRedirectMiddleware;
use EricFortmeyer\ActivityLog\Infrastructure\Auth\Auth0Adapter;
use EricFortmeyer\ActivityLog\Infrastructure\Auth\CallbackMiddleware;
use Nyholm\Psr7\Factory\Psr17Factory;
use PhpCommonEnums\HttpResponseCode\Enumeration\HttpResponseCodeEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

#[CoversClass(CallbackMiddleware::class)]
#[CoversClass(AbstractRedirectMiddleware::class)]
#[UsesClass(AppConfig::class)]
final class CallbackMiddlewareTest extends TestCase
{
    private UriInterface&Stub $uriStub;
    private ServerRequestInterface&Stub $requestStub;
    private ResponseInterface $responseStub;
    private ResponseFactoryInterface $responseFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->uriStub = $this->createStub(UriInterface::class);
        $this->requestStub = $this->createStub(ServerRequestInterface::class);
        $this->responseStub = $this->createStub(ResponseInterface::class);
        $this->responseFactory = new Psr17Factory();
    }

    #[Test]
    #[TestDox("Shall initiate the request handling when the request path is not the callback path")]
    #[TestWith([
        "requestPath" => "/some-path",
        "callbackPath" => "/another-path",
    ])]
    public function dfsijoa(
        string $requestPath,
        string $callbackPath,
    ) {
        $logger = $this->createStub(LoggerInterface::class);
        $auth0Adapter = $this->createStub(Auth0Adapter::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);

        $this->requestStub->method("getUri")->willReturn($this->uriStub);
        $this->uriStub->method("getPath")->willReturn($requestPath);
        $requestHandler
            ->expects($this->once())
            ->method("handle")
            ->willReturn($this->responseStub);

        $callbackMiddleware = new CallbackMiddleware(
            auth: $auth0Adapter,
            log: $logger,
            appConfig: new AppConfig(
                ["appName" => "", "callbackPath" => $callbackPath, "loginPath" => "", "logoutPath" => ""]
            ),
            responseFactory: $this->responseFactory,
        );

        $result = $callbackMiddleware->process($this->requestStub, $requestHandler);

        $this->assertEquals(
            $this->responseStub,
            $result
        );
    }

    #[Test]
    #[TestDox("Shall initiate the auth exchange and redirect to the return url when request matches callback path")]
    #[TestWith([
        "requestPath" => "/same-path",
        "callbackPath" => "/same-path",
        "hostname" => "FAKE.com",
        "expectedCallbackUrl" => "https://FAKE.com/same-path",
        "expectedReturnUrl" => "https://FAKE.com",
    ])]
    public function jefiwoq(
        string $requestPath,
        string $callbackPath,
        string $expectedCallbackUrl,
        string $expectedReturnUrl,
        string $hostname,
    ) {
        $logger = $this->createStub(LoggerInterface::class);
        $auth0Adapter = $this->createMock(Auth0Adapter::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);

        $this->requestStub->method("getUri")->willReturn($this->uriStub);
        $this->uriStub
            ->method("getPath")->willReturn($requestPath);
        $this->uriStub->method("getScheme")->willReturn("https");
        $this->uriStub->method("getHost")->willReturn($hostname);

        $requestHandler
            ->expects($this->never())
            ->method("handle");
        $auth0Adapter
            ->expects($this->once())
            ->method("exchange")
            ->with($expectedCallbackUrl);

        $callbackMiddleware = new CallbackMiddleware(
            auth: $auth0Adapter,
            log: $logger,
            appConfig: new AppConfig(
                ["appName" => "", "callbackPath" => $callbackPath, "loginPath" => "", "logoutPath" => ""]
            ),
            responseFactory: $this->responseFactory,
        );

        $result = $callbackMiddleware->process($this->requestStub, $requestHandler);

        $this->assertSame(
            $expectedReturnUrl,
            current($result->getHeader("Location")),
        );
        $this->assertSame(
            HttpResponseCodeEnum::TemporaryRedirect->value,
            $result->getStatusCode(),
        );
        $this->assertSame(
            HttpResponseCodeEnum::TemporaryRedirect->getLabel(),
            $result->getReasonPhrase(),
        );
    }

    #[Test]
    #[TestDox("Shall log the exception message and redirect to the logout path when a StateException is thrown")]
    #[TestWith([
        "requestPath" => "/same-path",
        "callbackPath" => "/same-path",
        "logoutPath" => "/logout",
        "hostname" => "FAKE.com",
        "exceptionMessage" => "TEST EXCEPTION"
    ])]
    public function fskpoad(
        string $requestPath,
        string $callbackPath,
        string $logoutPath,
        string $hostname,
        string $exceptionMessage,
    ) {
        $logger = $this->createMock(LoggerInterface::class);
        $auth0Adapter = $this->createMock(Auth0Adapter::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);

        $this->requestStub->method("getUri")->willReturn($this->uriStub);
        $this->uriStub
            ->method("getPath")->willReturn($requestPath);
        $this->uriStub->method("getScheme")->willReturn("https");
        $this->uriStub->method("getHost")->willReturn($hostname);

        $requestHandler
            ->expects($this->never())
            ->method("handle");
        $auth0Adapter
            ->expects($this->once())
            ->method("exchange")
            ->willThrowException(new StateException($exceptionMessage));
        $logger
            ->expects($this->once())
            ->method("critical")
            ->with($exceptionMessage);

        $callbackMiddleware = new CallbackMiddleware(
            auth: $auth0Adapter,
            log: $logger,
            appConfig: new AppConfig(
                ["appName" => "", "callbackPath" => $callbackPath, "loginPath" => "", "logoutPath" => $logoutPath]
            ),
            responseFactory: $this->responseFactory,
        );

        $result = $callbackMiddleware->process($this->requestStub, $requestHandler);

        $this->assertSame(
            $logoutPath,
            current($result->getHeader("Location")),
        );
        $this->assertSame(
            HttpResponseCodeEnum::TemporaryRedirect->value,
            $result->getStatusCode(),
        );
        $this->assertSame(
            HttpResponseCodeEnum::TemporaryRedirect->getLabel(),
            $result->getReasonPhrase(),
        );
    }
}
