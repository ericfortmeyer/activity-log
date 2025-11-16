<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\UserInterface\Contexts;

abstract class AbstractContext
{
    public function __construct(public string $title) {}
}
