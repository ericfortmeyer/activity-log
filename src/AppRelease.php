<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\AbstractModel;
use Phpolar\Validators\Pattern;

final class AppRelease extends AbstractModel
{
    #[Pattern("/^\d+\.\d+\.\d+$/")]
    public string $tagName;

    public function __construct(null|array|object $data = [])
    {
        parent::__construct($data);

        if (is_object($data) === true && property_exists($data, "tag_name") === true) {
            $this->tagName = $data->tag_name;
        }
    }
}
