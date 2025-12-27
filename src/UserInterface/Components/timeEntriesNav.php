<?php

namespace EricFortmeyer\ActivityLog\UserInterface\Components;

use EricFortmeyer\ActivityLog\UserInterface\Contexts\TimeEntriesContext;

/**
 * @codeCoverageIgnore
 */
function timeEntriesNav(TimeEntriesContext $view): string
{
    return <<<HTML
    <nav>
        <ul>
            <li><a href="/?{$view->getPreviousMonthFilter()}" data-tooltip="Previous" role="button">&#x23F4;</a></li>
        </ul>
        <ul>
            <li class="{$view->getCurrentMonthButtonClass()}">
                <a href="/" data-tooltip="Current Month" role="button">Current Month</a>
            </li>
        </ul>
        <ul>
            <li class="{$view->getNextMonthButtonClass()}">
                <a href="/?{$view->getNextMonthFilter()}" data-tooltip="Next" role="button">&#x23F5;</a>
            </li>
        </ul>
    </nav>
    HTML;
}
