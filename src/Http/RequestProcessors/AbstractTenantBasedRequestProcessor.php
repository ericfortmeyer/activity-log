<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use Phpolar\Phpolar\Auth\AbstractRestrictedAccessRequestProcessor;
use SensitiveParameter;

use function sodium_bin2hex;
use function sodium_crypto_generichash;

use const SODIUM_CRYPTO_GENERICHASH_BYTES_MIN;

abstract class AbstractTenantBasedRequestProcessor extends AbstractRestrictedAccessRequestProcessor
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
