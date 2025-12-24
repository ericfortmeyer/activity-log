<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UserInterface\HTMLElements;

use EricFortmeyer\ActivityLog\UserInterface\Contexts\AbstractContext;

/**
 * Contains machine-readable metadata about the document.
 *
 * @codeCoverageIgnore
 */
function head(AbstractContext $ctx, string $dialogScript = ""): string
{
    return <<<HTML
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{$ctx->title}</title>
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="stylesheet" href="/resources/css/pico.min.css">
        <link rel="stylesheet" href="/resources/css/overrides.css">
        {$dialogScript}
    </head>
HTML;
}
