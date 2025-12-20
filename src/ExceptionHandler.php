<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Phpolar\ExceptionHandlerInterface;
use Phpolar\Phpolar\Http\EmptyResponse;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class ExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function handle(Throwable $e): EmptyResponse
    {
        $this->logger->alert(
            "Exception",
            [
                "exception" => $e->getMessage(),
                "stacktrace" => $e->getTrace()
            ]
        );

        return new EmptyResponse();
    }
}
