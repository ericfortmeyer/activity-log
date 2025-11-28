<?php

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\AbstractModel;
use Phpolar\Model\PrimaryKey;

final class AppConfig extends AbstractModel
{
    #[PrimaryKey]
    public string $id;

    public string $appName;

    public string $callbackPath;

    public string $loginPath;

    public string $logoutPath;
}
