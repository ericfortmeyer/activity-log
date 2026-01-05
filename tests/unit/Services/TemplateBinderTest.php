<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use Phpolar\PurePhp\Binder;
use Phpolar\PurePhp\Dispatcher;
use Phpolar\PurePhp\StreamContentStrategy;
use Phpolar\PurePhp\TemplateEngine;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(TemplateBinder::class)]
final class TemplateBinderTest extends TestCase
{
    #[Test]
    #[TestDox("Shall throw the expected exception when the template file cannot be found")]
    #[TestWith([
        "Template NON_EXISTING_FILE could not be found",
        "NON_EXISTING_FILE",
    ])]
    public function throwsFileNotFound(
        string $exceptionMessage,
        string $nonexistingFile,
    ) {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $sut = new TemplateBinder(
            new TemplateEngine()
        );

        $sut->apply($nonexistingFile, (object)[]);
    }

    #[Test]
    #[TestDox("Shall throw the expected exception when the template file cannot be found")]
    #[TestWith([
        "Binding the context to the template failed.",
    ])]
    public function throwsBindFailed(
        string $exceptionMessage,
    ) {
        $templateFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "test.phtml";

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $binderStub = $this->createStub(Binder::class);
        $binderStub->method("bind")->willReturn(false);

        $sut = new TemplateBinder(
            new TemplateEngine(
                renderingAlgoFactory: new StreamContentStrategy(),
                binder: $binderStub,
                dispatcher: new Dispatcher(),
            )
        );

        $sut->apply($templateFile, (object)[]);
    }

    #[Test]
    #[TestDox("Shall throw the expected exception when the template file cannot be found")]
    #[TestWith([
        <<<HTML
        <html>
            <head><title>TEST&lowbar;TITLE</title></head>
            <body>
                <div>TEST&lowbar;MESSAGE</div>
            </body>
        </html>

        HTML,
        [
            "title" => "TEST_TITLE",
            "message" => "TEST_MESSAGE",
        ],
    ])]
    public function returnsResult(
        string $expectedResult,
        array $context,
    ) {
        $templateFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "test.phtml";


        $sut = new TemplateBinder(new TemplateEngine());

        $result = $sut->apply($templateFile, (object) $context);

        $this->assertSame($expectedResult, $result);
    }
}
