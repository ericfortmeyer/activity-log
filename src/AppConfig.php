<?php

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\AbstractModel;
use Phpolar\Model\PrimaryKey;

/**
 * @phan-file-suppress PhanReadOnlyPublicProperty
 */
final class AppConfig extends AbstractModel
{
    /**
     * @suppress PhanUnreferencedPublicProperty
     */
    #[PrimaryKey]
    public string $id;

    public string $appName;

    public string $callbackPath;

    public string $loginPath;

    public string $logoutPath;
}
