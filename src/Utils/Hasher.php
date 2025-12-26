<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Utils;

use SensitiveParameter;

use function sodium_bin2hex;
use function sodium_crypto_generichash;

use const SODIUM_CRYPTO_GENERICHASH_BYTES_MIN;

readonly class Hasher
{
    public function __construct(
        #[SensitiveParameter]
        private string $hashingKey,
        #[SensitiveParameter]
        private string $signingKey,
    ) {}

    public function hash(
        string $value
    ): string {
        return sodium_bin2hex(
            $this->hashingKey !== ""
                ?
                sodium_crypto_generichash(
                    message: $value,
                    key: $this->hashingKey,
                    length: SODIUM_CRYPTO_GENERICHASH_BYTES_MIN,
                ) :
                sodium_crypto_generichash(
                    message: $value,
                    length: SODIUM_CRYPTO_GENERICHASH_BYTES_MIN,
                )
        );
    }

    public function verify(
        string $data,
        string $signature,
    ): bool {
        return hash_equals(
            hash_hmac(
                "sha256",
                $data,
                $this->signingKey,
            ),
            $signature,
        );
    }
}
