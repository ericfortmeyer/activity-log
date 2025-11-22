<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UnitTests\Infrastructure\Auth;

use Auth0\SDK\Exception\ConfigurationException;
use Auth0\SDK\Exception\StateException;
use EricFortmeyer\ActivityLog\AppConfig;
use EricFortmeyer\ActivityLog\Http\AbstractRedirectMiddleware;
use EricFortmeyer\ActivityLog\Infrastructure\Auth\Auth0Adapter;
use EricFortmeyer\ActivityLog\Infrastructure\Auth\CallbackMiddleware;
use EricFortmeyer\ActivityLog\Infrastructure\Auth\LogoutMiddleware;
use Nyholm\Psr7\Factory\Psr17Factory;
use PhpCommonEnums\HttpResponseCode\Enumeration\HttpResponseCodeEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
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

#[CoversClass(LogoutMiddleware::class)]
#[CoversClass(AbstractRedirectMiddleware::class)]
#[UsesClass(AppConfig::class)]
final class LogoutMiddlewareTest extends TestCase
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
    #[TestCase("Shall initiate the request handling when the request path is not the callback path")]
    #[TestWith([
        "requestPath" => "/some-path",
        "logoutPath" => "/another-path",
    ])]
    public function dfsijoa(
        string $requestPath,
        string $logoutPath,
    ) {
        $this->requestStub->method("getUri")->willReturn($this->uriStub);
        $this->uriStub->method("getPath")->willReturn($requestPath);
        $this->requestHandler
            ->expects($this->once())
            ->method("handle")
            ->willReturn($this->responseStub);

        $middleware = new LogoutMiddleware(
            auth: $this->auth0Adapter,
            log: $this->logger,
            appConfig: new AppConfig(appName: "", callbackPath: "", loginPath: "", logoutPath: $logoutPath),
            responseFactory: $this->responseFactory,
        );

        $result = $middleware->process($this->requestStub, $this->requestHandler);

        $this->assertEquals(
            $this->responseStub,
            $result
        );
    }

    #[Test]
    #[TestCase("Shall initiate the auth exchange and redirect to the return url when request matches callback path")]
    #[TestWith([
        "requestPath" => "/same-path",
        "logoutPath" => "/same-path",
        "expectedCallbackUrl" => "https://FAKE.com/same-path",
        "expectedLogoutUrl" => "https://FAKE.com",
        "hostname" => "FAKE.com",
    ])]
    public function jefiwoq(
        string $requestPath,
        string $logoutPath,
        string $expectedCallbackUrl,
        string $expectedLogoutUrl,
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
            ->method("logout")
            ->with($expectedLogoutUrl)
            ->willReturn($expectedLogoutUrl);

        $middleware = new LogoutMiddleware(
            auth: $this->auth0Adapter,
            log: $this->logger,
            appConfig: new AppConfig(appName: "", callbackPath: "", loginPath: "", logoutPath: $logoutPath),
            responseFactory: $this->responseFactory,
        );

        $result = $middleware->process($this->requestStub, $this->requestHandler);

        $this->assertSame(
            $expectedLogoutUrl,
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
    #[TestCase("Shall log the exception message and redirect to the logout path when a StateException is thrown")]
    #[TestWith([
        "requestPath" => "/same-path",
        "logoutPath" => "/same-path",
        "loginPath" => "/login",
        "hostname" => "FAKE.com",
        "exceptionMessage" => "TEST EXCEPTION"
    ])]
    public function fskpoad(
        string $requestPath,
        string $logoutPath,
        string $loginPath,
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
            ->method("logout")
            ->willThrowException(new ConfigurationException($exceptionMessage));
        $this->logger
            ->expects($this->once())
            ->method("critical")
            ->with($exceptionMessage);

        $middleware = new LogoutMiddleware(
            auth: $this->auth0Adapter,
            log: $this->logger,
            appConfig: new AppConfig(appName: "", callbackPath: "", loginPath: $loginPath, logoutPath: $logoutPath),
            responseFactory: $this->responseFactory,
        );

        $result = $middleware->process($this->requestStub, $this->requestHandler);

        $this->assertSame(
            $loginPath,
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
