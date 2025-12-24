<?php

namespace EricFortmeyer\ActivityLog\UserInterface\Components;

use Phpolar\Model\InputTypes;

/**
 * @codeCoverageIgnore
 */
function formInput(
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
    $placeholder = $isInvalid && $type !== InputTypes::Number ? $errorMessage : $placeholder;
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
    $numberErrorMessage = $isInvalid && $name === "hours" ? <<<HTML
    <span style="color: var(--pico-del-color);">{$errorMessage}</span>
    HTML : "";

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
        {$numberErrorMessage}
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
