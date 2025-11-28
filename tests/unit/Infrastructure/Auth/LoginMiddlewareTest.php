<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UnitTests\Infrastructure\Auth;

use Auth0\SDK\Exception\ConfigurationException;
use EricFortmeyer\ActivityLog\AppConfig;
use EricFortmeyer\ActivityLog\Http\AbstractRedirectMiddleware;
use EricFortmeyer\ActivityLog\Infrastructure\Auth\Auth0Adapter;
use EricFortmeyer\ActivityLog\Infrastructure\Auth\LoginMiddleware;
use Nyholm\Psr7\Factory\Psr17Factory;
use PhpCommonEnums\HttpResponseCode\Enumeration\HttpResponseCodeEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

#[CoversClass(LoginMiddleware::class)]
#[CoversClass(AbstractRedirectMiddleware::class)]
#[UsesClass(AppConfig::class)]
final class LoginMiddlewareTest extends TestCase
{
    private UriInterface&Stub $uriStub;
    private ServerRequestInterface&Stub $requestStub;
    private ResponseInterface $responseStub;
    private Auth0Adapter&MockObject $auth0Adapter;
    private LoggerInterface&MockObject $logger;
    private ResponseFactoryInterface $responseFactory;
    private RequestHandlerInterface&MockObject $requestHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->uriStub = $this->createStub(UriInterface::class);
        $this->requestStub = $this->createStub(ServerRequestInterface::class);
        $this->responseStub = $this->createStub(ResponseInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->auth0Adapter = $this->createMock(Auth0Adapter::class);
        $this->requestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->responseFactory = new Psr17Factory();
    }

    #[Test]
    #[TestDox("Shall initiate the request handling when the request path is not the callback path")]
    #[TestWith([
        "requestPath" => "/some-path",
        "loginPath" => "/another-path",
    ])]
    public function dfsijoa(
        string $requestPath,
        string $loginPath,
    ) {
        $this->requestStub->method("getUri")->willReturn($this->uriStub);
        $this->uriStub->method("getPath")->willReturn($requestPath);
        $this->requestHandler
            ->expects($this->once())
            ->method("handle")
            ->willReturn($this->responseStub);

        $middleware = new LoginMiddleware(
            auth: $this->auth0Adapter,
            log: $this->logger,
            appConfig: new AppConfig(
                ["appName" => "", "callbackPath" => "", "loginPath" => $loginPath, "logoutPath" => ""]
            ),
            responseFactory: $this->responseFactory,
        );

        $result = $middleware->process($this->requestStub, $this->requestHandler);

        $this->assertEquals(
            $this->responseStub,
            $result
        );
    }

    #[Test]
    #[TestDox("Shall initiate the auth exchange and redirect to the return url when request matches callback path")]
    #[TestWith([
        "requestPath" => "/same-path",
        "loginPath" => "/same-path",
        "callbackPath" => "/callback-path",
        "expectedLoginUrl" => "https://FAKE.com/callback-path",
        "expectedCallbackUrl" => "https://FAKE.com/same-path",
        "hostname" => "FAKE.com",
    ])]
    public function jefiwoq(
        string $requestPath,
        string $loginPath,
        string $callbackPath,
        string $expectedCallbackUrl,
        string $expectedLoginUrl,
        string $hostname,
    ) {
        $this->requestStub->method("getUri")->willReturn($this->uriStub);
        $this->uriStub
            ->method("getPath")->willReturn($requestPath);
        $this->uriStub->method("getScheme")->willReturn("https");
        $this->uriStub->method("getHost")->willReturn($hostname);

        $this->requestHandler
            ->expects($this->never())
            ->method("handle");
        $this->auth0Adapter
            ->expects($this->once())
            ->method("clear");
        $this->auth0Adapter
            ->expects($this->once())
            ->method("login")
            ->with($expectedLoginUrl)
            ->willReturn($expectedLoginUrl);

        $middleware = new LoginMiddleware(
            auth: $this->auth0Adapter,
            log: $this->logger,
            appConfig: new AppConfig(
                ["appName" => "", "callbackPath" => $callbackPath, "loginPath" => $loginPath, "logoutPath" => ""]
            ),
            responseFactory: $this->responseFactory,
        );

        $result = $middleware->process($this->requestStub, $this->requestHandler);

        $this->assertSame(
            $expectedLoginUrl,
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
    #[TestDox("Shall log the exception message and redirect to logout when a ConfigurationException is thrown")]
    #[TestWith([
        "requestPath" => "/same-path",
        "callbackPath" => "/callback-path",
        "loginPath" => "/same-path",
        "logoutPath" => "/logout",
        "hostname" => "FAKE.com",
        "exceptionMessage" => "TEST EXCEPTION"
    ])]
    public function fskpoad(
        string $requestPath,
        string $callbackPath,
        string $loginPath,
        string $logoutPath,
        string $hostname,
        string $exceptionMessage,
    ) {
        $this->requestStub->method("getUri")->willReturn($this->uriStub);
        $this->uriStub
            ->method("getPath")->willReturn($requestPath);
        $this->uriStub->method("getScheme")->willReturn("https");
        $this->uriStub->method("getHost")->willReturn($hostname);

        $this->requestHandler
            ->expects($this->never())
            ->method("handle");
        $this->auth0Adapter
            ->expects($this->once())
            ->method("login")
            ->willThrowException(new ConfigurationException($exceptionMessage));
        $this->logger
            ->expects($this->once())
            ->method("critical")
            ->with($exceptionMessage);

        $middleware = new LoginMiddleware(
            auth: $this->auth0Adapter,
            log: $this->logger,
            appConfig: new AppConfig(
                [
                    "appName" => "",
                    "callbackPath" => $callbackPath,
                    "loginPath" => $loginPath,
                    "logoutPath" => $logoutPath
                ]
            ),
            responseFactory: $this->responseFactory,
        );

        $result = $middleware->process($this->requestStub, $this->requestHandler);

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
