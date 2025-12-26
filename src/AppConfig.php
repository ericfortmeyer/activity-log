<?php

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\AbstractModel;
use Phpolar\Model\PrimaryKey;
use Phpolar\Validators\Pattern;

final class AppConfig extends AbstractModel
{
    #[PrimaryKey]
    public string $id;

    public string $appName;

    public string $callbackPath;

    public string $loginPath;

    public string $logoutPath;

    #[Pattern("/^\d+\.\d+\.\d+$/")]
    public string $version;
}
