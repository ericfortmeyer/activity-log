<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Exception;
use Phpolar\Phpolar\Http\EmptyResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(ExceptionHandler::class)]
final class ExceptionHandlerTest extends TestCase
{
    #[Test]
    #[TestDox("Notifies exception occurred and returns empty response")]
    #[TestWith(["Exception Message"])]
    public function notifies(
        string $expectedExceptionMessage,
    ) {
        $exception = new Exception($expectedExceptionMessage);
        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())
            ->method("alert")
            ->with("Exception", [
                "exception" => $exception->getMessage(),
                "stacktrace" => $exception->getTrace(),
            ]);

        $sut = new ExceptionHandler($loggerSpy);

        $response = $sut->handle($exception);

        $this->assertInstanceOf(EmptyResponse::class, $response);
    }
}
