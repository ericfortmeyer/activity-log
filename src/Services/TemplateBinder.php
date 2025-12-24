<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Services;

use Phpolar\PurePhp\BindFailed;
use Phpolar\PurePhp\FileNotFound;
use Phpolar\PurePhp\HtmlSafeContext;
use Phpolar\PurePhp\TemplateEngine;
use RuntimeException;

final readonly class TemplateBinder
{
    public function __construct(
        private TemplateEngine $templateEngine,
    ) {}

    /**
     * @throws RuntimeException The template file could not be found or binding failed.
     */
    public function apply(
        string $template,
        object $context,
    ): string {
        $bindResult = $this->templateEngine->apply($template, new HtmlSafeContext($context));
        return match (true) {
            $bindResult instanceof FileNotFound => throw new RuntimeException(
                sprintf("Template %s could not be found.", $template)
            ),
            $bindResult instanceof BindFailed => throw new RuntimeException(
                "Binding the context to the template failed."
            ),
            default => $bindResult,
        };
    }
}
