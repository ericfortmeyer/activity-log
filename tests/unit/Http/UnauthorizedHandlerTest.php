<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(UnauthorizedHandler::class)]
final class UnauthorizedHandlerTest extends TestCase
{
    #[Test]
    #[TestDox("Shall return the expected HTTP response")]
    public function returnsResponse()
    {
        $requestStub = $this->createStub(ServerRequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseFactoryMock = $this->createMock(
            ResponseFactoryInterface::class
        );

        $responseFactoryMock->expects($this->once())
            ->method("createResponse")
            ->with(307, "Temporary Redirect")
            ->willReturn($responseMock);

        $responseMock
            ->expects($this->once())
            ->method("withHeader")
            ->with("Location", "/")
            ->willReturnSelf();

        $responseMock
            ->method("getStatusCode")
            ->willReturn(307);

        $sut = new UnauthorizedHandler(
            "/",
            $responseFactoryMock,
        );

        $response = $sut->handle($requestStub);

        $this->assertSame(307, $response->getStatusCode());
    }
}
