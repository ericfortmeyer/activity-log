<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Templates\Layout;

function head(object $ctx, string $dialogScript = ""): string
{
    return <<<HTML
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{$ctx->getTitle()}</title>
        <link rel="stylesheet" href="/resources/css/pico.min.css">
        <link rel="stylesheet" href="/resources/css/overrides.css">
        {$dialogScript}
    </head>
HTML;
}
