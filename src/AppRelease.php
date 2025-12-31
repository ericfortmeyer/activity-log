<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\AbstractModel;
use Phpolar\Validators\MaxLength;
use Phpolar\Validators\MinLength;
use Phpolar\Validators\Pattern;
use Phpolar\Validators\Required;

final class AppRelease extends AbstractModel
{
    #[MinLength(8)]
    #[MaxLength(12)]
    #[Required]
    public int $id;

    #[Required]
    #[Pattern("/^[[:digit:]]+\.[[:digit:]]+\.[[:digit:]]+(?:-[[:alnum:]]+)*$/")]
    public string $tagName;

    public function __construct(null|array|object $data = [])
    {
        parent::__construct($data);

        $this->tagName = match (true) {
            is_object($data) === true
                && property_exists($data, "tag_name") === true => $data->tag_name,
            is_array($data) === true
                && array_key_exists("tag_name", $data) === true => $data["tag_name"],
            default => ""
        };
    }
}
