<?php

declare(strict_types=1);

use EricFortmeyer\ActivityLog\Services\TemplateBinder;
use Phpolar\PurePhp\TemplateEngine;

return [
    TemplateEngine::class => new TemplateEngine(),
    TemplateBinder::class => new TemplateBinder(
        new TemplateEngine()
    ),
];
