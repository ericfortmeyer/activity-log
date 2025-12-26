<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

enum AppReleaseAction: string
{
    case Created = "created";
    case Deleted = "deleted";
    case Published = "published";
    case Unknown = "Unknown";
}
