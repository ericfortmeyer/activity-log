<?php

namespace EricFortmeyer\ActivityLog\Templates\Components;

use Phpolar\Model\InputTypes;

function input(
    string $name,
    string $label,
    InputTypes $type = InputTypes::Text,
    string|int|null $value = null,
    string $placeholder = "",
    string $errorMessage = "",
    bool $isInvalid = false,
    string $selectValAttr = "",
    bool $required = false
): string {
    $requiredAttr = $required ? "required" : "";
    $placeholder = $isInvalid ? $errorMessage : $placeholder;
    $type = $type->asString();
    $min = $type === InputTypes::Number->asString() ? "min=\"0\"" : "";
    $max = sprintf(
        "max=\"%s\"",
        match ($name) {
            "minutes" => 60,
            "hours" => 24,
            "dayOfMonth" => 31,
            "month" => 12,
            default => PHP_INT_MAX,
        },
    );

    return $type === InputTypes::Number->asString() ? <<<HTML
    <div class="form-group">
        <label for="{$name}">{$label}</label>
        <input
            type="{$type}"
            id="{$name}"
            name="{$name}"
            value="{$value}"
            {$min}
            {$max}
            {$requiredAttr} 
            {$selectValAttr}
            placeholder="{$placeholder}" 
        />
    </div>
    HTML : <<<HTML
    <div class="form-group">
        <label for="{$name}">{$label}</label>
        <input 
            type="{$type}" 
            id="{$name}" 
            name="{$name}" 
            value="{$value}" 
            placeholder={$placeholder} {$requiredAttr} {$selectValAttr} 
        />
    </div>
    HTML;
}
