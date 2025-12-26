<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\AbstractModel;

final class AppReleaseEvent extends AbstractModel
{
    public readonly AppReleaseAction $action;
    public readonly AppRelease $release;

    public function __construct(null|array|object $data = [])
    {
        $this->action = is_object($data) === true && property_exists($data, "action") === true
            ? AppReleaseAction::from($data->action)
            : AppReleaseAction::Unknown;

        $this->release = new AppRelease(
            is_object($data) === true && property_exists($data, "release") === true
                ? $data->release
                : []
        );
    }
}
