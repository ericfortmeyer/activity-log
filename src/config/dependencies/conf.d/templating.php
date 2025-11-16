<?php

declare(strict_types=1);

use Phpolar\PurePhp\TemplateEngine;

return [
    TemplateEngine::class => static fn() => new TemplateEngine(),
];
