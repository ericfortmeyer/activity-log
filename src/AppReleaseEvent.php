<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog;

use Phpolar\Model\AbstractModel;
use Phpolar\Validators\MaxLength;
use Phpolar\Validators\Pattern;
use Psr\Http\Message\RequestInterface;

final class AppReleaseEvent extends AbstractModel
{
    private const EVENT_TYPE_HEADER_KEY = "X-GitHub-Event";
    private const HOOK_ID_HEADER_KEY = "X-GitHub-Hook-ID";
    private const RELEASE_EVENT_TYPE = "release";
    private const ACCEPTABLE_RELEASE_ACTION = ["published"];
    public readonly AppRelease $release;

    #[MaxLength(100)]
    #[Pattern("/^[[:digit:]]+$/")]
    public int|string $hookId;

    public function __construct(null|array|object $data = [])
    {
        $this->release = new AppRelease(
            is_object($data) === true && property_exists($data, "release") === true
                ? $data->release
                : []
        );
    }

    public function isValid(): bool
    {
        return true;
    }

    public static function fromRequest(string $requestBody, RequestInterface $request): self
    {
        $data = json_decode($requestBody);
        return new self(is_object($data) ? $data : [])->withHookId(
            $request->getHeader(self::HOOK_ID_HEADER_KEY)[0] ?? "invalid!!!"
        );
    }

    public static function isReleaseEventRequest(RequestInterface $request): bool
    {
        return ($request->getHeader(self::EVENT_TYPE_HEADER_KEY)[0] ?? "invalid!!!") === self::RELEASE_EVENT_TYPE;
    }

    public static function isCreatedRelease(string $requestBody): bool
    {
        $json = json_decode($requestBody);
        return is_object($json) === true
            && property_exists($json, "action") === true
            && in_array($json->action, self::ACCEPTABLE_RELEASE_ACTION);
    }

    private function withHookId(int|string $hookId): self
    {
        $newThis = clone $this;
        $newThis->hookId = $hookId;
        return $newThis;
    }
}
