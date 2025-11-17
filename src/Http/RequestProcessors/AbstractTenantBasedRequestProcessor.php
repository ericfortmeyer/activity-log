<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use Phpolar\Phpolar\Auth\AbstractProtectedRoutable;
use SensitiveParameter;

abstract class AbstractTenantBasedRequestProcessor extends AbstractProtectedRoutable
{
    public function __construct(
        #[SensitiveParameter]
        private readonly string $hashingKey,
    ) {}

    public function getTenantId(): string
    {
        return sodium_bin2hex(
            $this->hashingKey !== ""
                ?
                sodium_crypto_generichash(
                    message: $this->user->nickname,
                    key: $this->hashingKey,
                    length: SODIUM_CRYPTO_GENERICHASH_BYTES_MIN,
                ) :
                sodium_crypto_generichash(
                    message: $this->user->nickname,
                    length: SODIUM_CRYPTO_GENERICHASH_BYTES_MIN,
                )
        );
    }
}
