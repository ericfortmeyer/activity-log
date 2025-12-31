<?php

namespace EricFortmeyer\ActivityLog\UserInterface\Components;

function colorSchemeSwitcher(): string
{
    return <<<HTML
    <nav>
        <ul>
            <li>
                <details class="dropdown">
                    <summary>Theme</summary>
                    <ul dir="rtl">
                        <li><a href="#" data-theme-switcher="auto">Auto</a></li>
                        <li><a href="#" data-theme-switcher="light">Light</a></li>
                        <li><a href="#" data-theme-switcher="dark">Dark</a></li>
                    </ul>
                </details>
            </li>
        </ul>
    </nav>
    HTML;
}
