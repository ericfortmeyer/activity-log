<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\AbstractModel;
use Phpolar\Validators\MaxLength;
use Phpolar\Validators\Min;
use Phpolar\Validators\MinLength;
use Phpolar\Validators\Pattern;
use Phpolar\Validators\Required;
use Psr\Http\Message\RequestInterface;

final class AppReleaseEvent extends AbstractModel
{
    private const EVENT_TYPE_HEADER_KEY = "X-GitHub-Event";
    private const HOOK_ID_HEADER_KEY = "X-GitHub-Hook-ID";
    private const RELEASE_EVENT_TYPE = "release";
    public readonly AppRelease $release;

    #[Required]
    #[Min(1)]
    #[MinLength(8)]
    #[MaxLength(12)]
    public int|string $hookId;

    #[Required]
    #[Pattern("/^published$/")]
    public string $action;

    public function __construct(null|array|object $data = [])
    {
        $this->hookId = match (true) {
            is_object($data) === true
                && property_exists($data, "hookId") === true
            => $data->hookId,
            is_array($data) === true
                && array_key_exists("hookId", $data) === true
            => $data["hookId"],
            default => ""
        };

        $this->action = match (true) {
            is_object($data) === true
                && property_exists($data, "action") === true
            => $data->action,
            is_array($data) === true
                && array_key_exists("action", $data) === true
            => $data["action"],
            default => ""
        };

        $this->release = new AppRelease(
            match (true) {
                is_object($data) === true
                    && property_exists($data, "release") === true
                => $data->release,
                is_array($data) === true
                    && array_key_exists("release", $data) === true
                => $data["release"],
                default => [],
            }
        );
    }

    public function isValid(): bool
    {
        return $this->release->isValid() && parent::isValid();
    }

    public static function fromRequest(string $requestBody, RequestInterface $request): self
    {
        $data = json_decode($requestBody);
        if (is_object($data) === false) {
            return new self();
        }

        $event = new self($data);
        $event->hookId = $request->getHeader(self::HOOK_ID_HEADER_KEY)[0] ?? "invalid!!!";
        return $event;
    }

    public static function isReleaseEventRequest(RequestInterface $request): bool
    {
        return ($request->getHeader(self::EVENT_TYPE_HEADER_KEY)[0] ?? "invalid!!!") === self::RELEASE_EVENT_TYPE;
    }
}
