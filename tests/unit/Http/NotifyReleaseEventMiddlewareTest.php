<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http;

use EricFortmeyer\ActivityLog\AppReleaseEvent;
use EricFortmeyer\ActivityLog\Services\AppConfigService;
use EricFortmeyer\ActivityLog\Utils\Hasher;
use PhpCommonEnums\HttpMethod\Enumeration\HttpMethodEnum;
use PhpCommonEnums\HttpResponseCode\Enumeration\HttpResponseCodeEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(NotifyReleaseEventMiddleware::class)]
#[UsesClass(AppReleaseEvent::class)]
final class NotifyReleaseEventMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        $fileName = dirname(__DIR__) . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "12345678.json";

        file_exists($fileName) && unlink($fileName);
    }

    #[Test]
    #[TestDox("Shall process next middleware when the path does not match")]
    #[TestWith(["/hooks/release", "/some/other/path"])]
    public function processesNext(
        string $hooksPath,
        string $nonMatchingPath,
    ) {
        $uriStub = $this->createStub(UriInterface::class);
        $streamStub = $this->createStub(StreamInterface::class);
        $requestStub = $this->createStub(ServerRequestInterface::class);
        $responseStub = $this->createStub(ResponseInterface::class);
        $requestStub->method("getUri")->willReturn($uriStub);
        $requestStub->method("getBody")->willReturn($streamStub);
        $uriStub->method("getPath")->willReturn($nonMatchingPath);
        $streamStub->method("getContents")->willReturn("");
        $nextRequestHandlerSpy = $this->createMock(RequestHandlerInterface::class);
        $nextRequestHandlerSpy->expects($this->once())
            ->method("handle")
            ->with($requestStub)
            ->willReturn($responseStub);

        $sut = new NotifyReleaseEventMiddleware(
            releaseEventHookPath: $hooksPath,
            releaseEventDestination: "",
            responseFactory: $this->createStub(ResponseFactoryInterface::class),
            appVersionUpdater: $this->createStub(AppConfigService::class),
            verifier: $this->createStub(Hasher::class),
        );

        $response = $sut->process($requestStub, $nextRequestHandlerSpy);

        $this->assertEquals($responseStub, $response);
    }

    #[Test]
    #[TestDox("Shall respond with 'Method Not Allowed' when the request method is not supported")]
    #[TestWith(["/hooks/release", "/hooks/release", HttpMethodEnum::Get])]
    public function respondsNotAllowed(
        string $hooksPath,
        string $matchingPath,
        HttpMethodEnum $nonSupportedMethod,
    ) {
        $uriStub = $this->createStub(UriInterface::class);
        $streamStub = $this->createStub(StreamInterface::class);
        $requestStub = $this->createStub(ServerRequestInterface::class);
        $responseStub = $this->createStub(ResponseInterface::class);
        $requestStub->method("getUri")->willReturn($uriStub);
        $requestStub->method("getBody")->willReturn($streamStub);
        $requestStub->method("getMethod")->willReturn($nonSupportedMethod->value);
        $uriStub->method("getPath")->willReturn($matchingPath);
        $streamStub->method("getContents")->willReturn("");
        $nextRequestHandlerSpy = $this->createMock(RequestHandlerInterface::class);
        $nextRequestHandlerSpy->expects($this->never())
            ->method("handle")
            ->with($requestStub);
        $responseFactorySpy = $this->createMock(ResponseFactoryInterface::class);
        $responseFactorySpy->expects($this->once())
            ->method("createResponse")
            ->with(HttpResponseCodeEnum::MethodNotAllowed->value, HttpResponseCodeEnum::MethodNotAllowed->name)
            ->willReturn($responseStub);

        $sut = new NotifyReleaseEventMiddleware(
            releaseEventHookPath: $hooksPath,
            releaseEventDestination: "",
            responseFactory: $responseFactorySpy,
            appVersionUpdater: $this->createStub(AppConfigService::class),
            verifier: $this->createStub(Hasher::class),
        );

        $response = $sut->process($requestStub, $nextRequestHandlerSpy);

        $this->assertEquals($responseStub, $response);
    }

    #[Test]
    #[TestDox("Shall respond with 'Unauthorized' when the request user agent is not supported")]
    #[TestWith([
        "/hooks/release",
        "/hooks/release",
        HttpMethodEnum::Post,
    ])]
    public function respondsUnauth0(
        string $hooksPath,
        string $matchingPath,
        HttpMethodEnum $supportedMethod,
    ) {
        $uriStub = $this->createStub(UriInterface::class);
        $streamStub = $this->createStub(StreamInterface::class);
        $requestStub = $this->createStub(ServerRequestInterface::class);
        $responseStub = $this->createStub(ResponseInterface::class);
        $requestStub->method("getUri")->willReturn($uriStub);
        $requestStub->method("getBody")->willReturn($streamStub);
        $requestStub->method("getMethod")->willReturn($supportedMethod->value);
        $uriStub->method("getPath")->willReturn($matchingPath);
        $streamStub->method("getContents")->willReturn("");
        $nextRequestHandlerSpy = $this->createMock(RequestHandlerInterface::class);
        $nextRequestHandlerSpy->expects($this->never())
            ->method("handle")
            ->with($requestStub);
        $responseFactorySpy = $this->createMock(ResponseFactoryInterface::class);
        $responseFactorySpy->expects($this->once())
            ->method("createResponse")
            ->with(
                HttpResponseCodeEnum::Unauthorized->value,
                HttpResponseCodeEnum::Unauthorized->name
            )
            ->willReturn($responseStub);

        $sut = new NotifyReleaseEventMiddleware(
            releaseEventHookPath: $hooksPath,
            releaseEventDestination: "",
            responseFactory: $responseFactorySpy,
            appVersionUpdater: $this->createStub(AppConfigService::class),
            verifier: $this->createStub(Hasher::class),
        );

        $response = $sut->process($requestStub, $nextRequestHandlerSpy);

        $this->assertEquals($responseStub, $response);
    }

    #[Test]
    #[TestDox("Shall response with 'Unauthorized' when the request body does not pass signature check")]
    #[TestWith([
        "/hooks/release",
        "/hooks/release",
        HttpMethodEnum::Post,
        "GitHub-Hookshot",
        "sha256=xxxxxxx",
        "xxxxxxx",
        "{\"action\":\"published\"}"
    ])]
    public function respondsUnauth1(
        string $hooksPath,
        string $matchingPath,
        HttpMethodEnum $supportedMethod,
        string $allowedUserAgent,
        string $signatureHeaderValue,
        string $signature,
        string $requestBody,
    ) {
        $uriStub = $this->createStub(UriInterface::class);
        $streamStub = $this->createStub(StreamInterface::class);
        $requestStub = $this->createStub(ServerRequestInterface::class);
        $responseStub = $this->createStub(ResponseInterface::class);
        $requestStub->method("getUri")->willReturn($uriStub);
        $requestStub->method("getBody")->willReturn($streamStub);
        $requestStub
            ->method("getHeader")
            ->willReturnOnConsecutiveCalls(
                [$allowedUserAgent],
                [$signatureHeaderValue],
            );
        $requestStub->method("getMethod")->willReturn($supportedMethod->value);
        $uriStub->method("getPath")->willReturn($matchingPath);
        $streamStub->method("getContents")->willReturn($requestBody);
        $nextRequestHandlerSpy = $this->createMock(RequestHandlerInterface::class);
        $nextRequestHandlerSpy->expects($this->never())
            ->method("handle")
            ->with($requestStub);
        $responseFactorySpy = $this->createMock(ResponseFactoryInterface::class);
        $responseFactorySpy->expects($this->once())
            ->method("createResponse")
            ->with(
                HttpResponseCodeEnum::Unauthorized->value,
                HttpResponseCodeEnum::Unauthorized->name
            )
            ->willReturn($responseStub);

        $verifierSpy = $this->createMock(Hasher::class);
        $verifierSpy->expects($this->once())
            ->method("verify")
            ->with($requestBody, $signature)
            ->willReturn(false);

        $sut = new NotifyReleaseEventMiddleware(
            releaseEventHookPath: $hooksPath,
            releaseEventDestination: "",
            responseFactory: $responseFactorySpy,
            appVersionUpdater: $this->createStub(AppConfigService::class),
            verifier: $verifierSpy,
        );

        $response = $sut->process($requestStub, $nextRequestHandlerSpy);

        $this->assertEquals($responseStub, $response);
    }

    #[Test]
    #[TestDox("Shall response with 'Not Implemented' when the hook event is not a release")]
    #[TestWith([
        "/hooks/release",
        "/hooks/release",
        HttpMethodEnum::Post,
        "GitHub-Hookshot",
        "sha256=xxxxxxx",
        "xxxxxxx",
        "{\"action\":\"published\"}",
        "push",
    ])]
    public function respondsNotImplemented(
        string $hooksPath,
        string $matchingPath,
        HttpMethodEnum $supportedMethod,
        string $allowedUserAgent,
        string $signatureHeaderValue,
        string $signature,
        string $requestBody,
        string $hookEventType,
    ) {
        $uriStub = $this->createStub(UriInterface::class);
        $streamStub = $this->createStub(StreamInterface::class);
        $requestStub = $this->createStub(ServerRequestInterface::class);
        $responseStub = $this->createStub(ResponseInterface::class);
        $requestStub->method("getUri")->willReturn($uriStub);
        $requestStub->method("getBody")->willReturn($streamStub);
        $requestStub
            ->method("getHeader")
            ->willReturnOnConsecutiveCalls(
                [$allowedUserAgent],
                [$signatureHeaderValue],
                [$hookEventType],
            );
        $requestStub->method("getMethod")->willReturn($supportedMethod->value);
        $uriStub->method("getPath")->willReturn($matchingPath);
        $streamStub->method("getContents")->willReturn($requestBody);
        $nextRequestHandlerSpy = $this->createMock(RequestHandlerInterface::class);
        $nextRequestHandlerSpy->expects($this->never())
            ->method("handle")
            ->with($requestStub);
        $responseFactorySpy = $this->createMock(ResponseFactoryInterface::class);
        $responseFactorySpy->expects($this->once())
            ->method("createResponse")
            ->with(
                HttpResponseCodeEnum::NotImplemented->value,
                HttpResponseCodeEnum::NotImplemented->name
            )
            ->willReturn($responseStub);

        $verifierSpy = $this->createMock(Hasher::class);
        $verifierSpy->expects($this->once())
            ->method("verify")
            ->with($requestBody, $signature)
            ->willReturn(true);

        $sut = new NotifyReleaseEventMiddleware(
            releaseEventHookPath: $hooksPath,
            releaseEventDestination: "",
            responseFactory: $responseFactorySpy,
            appVersionUpdater: $this->createStub(AppConfigService::class),
            verifier: $verifierSpy,
        );

        $response = $sut->process($requestStub, $nextRequestHandlerSpy);

        $this->assertEquals($responseStub, $response);
    }

    #[Test]
    #[TestDox("Shall response with 'Bad Request' when the request body is invalid")]
    #[TestWith([
        "/hooks/release",
        "/hooks/release",
        HttpMethodEnum::Post,
        "GitHub-Hookshot",
        "sha256=xxxxxxx",
        "xxxxxxx",
        "{\"invalid\":\"invalid\"}",
        "release",
        "hookID-1234",
    ])]
    public function respondsBadRequest(
        string $hooksPath,
        string $matchingPath,
        HttpMethodEnum $supportedMethod,
        string $allowedUserAgent,
        string $signatureHeaderValue,
        string $signature,
        string $requestBody,
        string $hookEventType,
        string $hookId,
    ) {
        $uriStub = $this->createStub(UriInterface::class);
        $streamStub = $this->createStub(StreamInterface::class);
        $requestStub = $this->createStub(ServerRequestInterface::class);
        $responseStub = $this->createStub(ResponseInterface::class);
        $requestStub->method("getUri")->willReturn($uriStub);
        $requestStub->method("getBody")->willReturn($streamStub);
        $requestStub
            ->method("getHeader")
            ->willReturnOnConsecutiveCalls(
                [$allowedUserAgent],
                [$signatureHeaderValue],
                [$hookEventType],
                [$hookId],
            );
        $requestStub->method("getMethod")->willReturn($supportedMethod->value);
        $uriStub->method("getPath")->willReturn($matchingPath);
        $streamStub->method("getContents")->willReturn($requestBody);
        $nextRequestHandlerSpy = $this->createMock(RequestHandlerInterface::class);
        $nextRequestHandlerSpy->expects($this->never())
            ->method("handle")
            ->with($requestStub);
        $responseFactorySpy = $this->createMock(ResponseFactoryInterface::class);
        $responseFactorySpy->expects($this->once())
            ->method("createResponse")
            ->with(
                HttpResponseCodeEnum::BadRequest->value,
                HttpResponseCodeEnum::BadRequest->name
            )
            ->willReturn($responseStub);

        $verifierSpy = $this->createMock(Hasher::class);
        $verifierSpy->expects($this->once())
            ->method("verify")
            ->with($requestBody, $signature)
            ->willReturn(true);

        $sut = new NotifyReleaseEventMiddleware(
            releaseEventHookPath: $hooksPath,
            releaseEventDestination: "",
            responseFactory: $responseFactorySpy,
            appVersionUpdater: $this->createStub(AppConfigService::class),
            verifier: $verifierSpy,
        );

        $response = $sut->process($requestStub, $nextRequestHandlerSpy);

        $this->assertEquals($responseStub, $response);
    }

    #[Test]
    #[TestDox("Shall response with 'Internal Server Error' when handling the release event fails")]
    #[TestWith([
        "/hooks/release",
        "/hooks/release",
        HttpMethodEnum::Post,
        "GitHub-Hookshot",
        "sha256=xxxxxxx",
        "xxxxxxx",
        "{\"action\":\"published\",\"release\":{\"id\":12345678,\"tag_name\":\"0.10.5\"}}",
        "release",
        "hookID-1234",
        "0.10.5",
    ])]
    public function respondsInternalServerError(
        string $hooksPath,
        string $matchingPath,
        HttpMethodEnum $supportedMethod,
        string $allowedUserAgent,
        string $signatureHeaderValue,
        string $signature,
        string $requestBody,
        string $hookEventType,
        string $hookId,
        string $tagName,
    ) {
        $uriStub = $this->createStub(UriInterface::class);
        $streamStub = $this->createStub(StreamInterface::class);
        $requestStub = $this->createStub(ServerRequestInterface::class);
        $responseStub = $this->createStub(ResponseInterface::class);
        $requestStub->method("getUri")->willReturn($uriStub);
        $requestStub->method("getBody")->willReturn($streamStub);
        $requestStub
            ->method("getHeader")
            ->willReturnOnConsecutiveCalls(
                [$allowedUserAgent],
                [$signatureHeaderValue],
                [$hookEventType],
                [$hookId],
                [$hookId],
            );
        $requestStub->method("getMethod")->willReturn($supportedMethod->value);
        $uriStub->method("getPath")->willReturn($matchingPath);
        $streamStub->method("getContents")->willReturn($requestBody);
        $nextRequestHandlerSpy = $this->createMock(RequestHandlerInterface::class);
        $nextRequestHandlerSpy->expects($this->never())
            ->method("handle")
            ->with($requestStub);
        $responseFactorySpy = $this->createMock(ResponseFactoryInterface::class);
        $responseFactorySpy->expects($this->once())
            ->method("createResponse")
            ->with(
                HttpResponseCodeEnum::InternalServerError->value,
                HttpResponseCodeEnum::InternalServerError->name
            )
            ->willReturn($responseStub);

        $verifierSpy = $this->createMock(Hasher::class);
        $verifierSpy->expects($this->once())
            ->method("verify")
            ->with($requestBody, $signature)
            ->willReturn(true);

        $appVersionUpdaterSpy = $this->createMock(AppConfigService::class);
        $appVersionUpdaterSpy->expects($this->once())
            ->method("updateVersion")
            ->with($tagName)
            ->willReturn(false);

        $sut = new NotifyReleaseEventMiddleware(
            releaseEventHookPath: $hooksPath,
            releaseEventDestination: dirname(__DIR__) . DIRECTORY_SEPARATOR . "files",
            responseFactory: $responseFactorySpy,
            appVersionUpdater: $appVersionUpdaterSpy,
            verifier: $verifierSpy,
        );

        $response = $sut->process($requestStub, $nextRequestHandlerSpy);

        $this->assertEquals($responseStub, $response);
    }

    #[Test]
    #[TestDox("Shall response with 'Accepted' when handling the release event succeeds")]
    #[TestWith([
        "/hooks/release",
        "/hooks/release",
        HttpMethodEnum::Post,
        "GitHub-Hookshot",
        "sha256=xxxxxxx",
        "xxxxxxx",
        "{\"action\":\"published\",\"release\":{\"id\":12345678,\"tag_name\":\"0.10.5\"}}",
        "release",
        "hookID-1234",
        "0.10.5",
    ])]
    public function respondsAccepted(
        string $hooksPath,
        string $matchingPath,
        HttpMethodEnum $supportedMethod,
        string $allowedUserAgent,
        string $signatureHeaderValue,
        string $signature,
        string $requestBody,
        string $hookEventType,
        string $hookId,
        string $tagName,
    ) {
        $uriStub = $this->createStub(UriInterface::class);
        $streamStub = $this->createStub(StreamInterface::class);
        $requestStub = $this->createStub(ServerRequestInterface::class);
        $responseStub = $this->createStub(ResponseInterface::class);
        $requestStub->method("getUri")->willReturn($uriStub);
        $requestStub->method("getBody")->willReturn($streamStub);
        $requestStub
            ->method("getHeader")
            ->willReturnOnConsecutiveCalls(
                [$allowedUserAgent],
                [$signatureHeaderValue],
                [$hookEventType],
                [$hookId],
                [$hookId],
            );
        $requestStub->method("getMethod")->willReturn($supportedMethod->value);
        $uriStub->method("getPath")->willReturn($matchingPath);
        $streamStub->method("getContents")->willReturn($requestBody);
        $nextRequestHandlerSpy = $this->createMock(RequestHandlerInterface::class);
        $nextRequestHandlerSpy->expects($this->never())
            ->method("handle")
            ->with($requestStub);
        $responseFactorySpy = $this->createMock(ResponseFactoryInterface::class);
        $responseFactorySpy->expects($this->once())
            ->method("createResponse")
            ->with(
                HttpResponseCodeEnum::Accepted->value,
                HttpResponseCodeEnum::Accepted->name
            )
            ->willReturn($responseStub);

        $verifierSpy = $this->createMock(Hasher::class);
        $verifierSpy->expects($this->once())
            ->method("verify")
            ->with($requestBody, $signature)
            ->willReturn(true);

        $appVersionUpdaterSpy = $this->createMock(AppConfigService::class);
        $appVersionUpdaterSpy->expects($this->once())
            ->method("updateVersion")
            ->with($tagName)
            ->willReturn(true);

        $sut = new NotifyReleaseEventMiddleware(
            releaseEventHookPath: $hooksPath,
            releaseEventDestination: dirname(__DIR__) . DIRECTORY_SEPARATOR . "files",
            responseFactory: $responseFactorySpy,
            appVersionUpdater: $appVersionUpdaterSpy,
            verifier: $verifierSpy,
        );

        $response = $sut->process($requestStub, $nextRequestHandlerSpy);

        $this->assertEquals($responseStub, $response);
    }
}
