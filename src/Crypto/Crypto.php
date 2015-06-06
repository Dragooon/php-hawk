<?php

namespace Dragooon\Hawk\Crypto;

use Dragooon\Hawk\Credentials\CredentialsInterface;

class Crypto
{
    const HEADER_VERSION = 1;

    /**
     * @param string $payload
     * @param string $algorithm
     * @param string $contentType
     * @return string
     */
    public function calculatePayloadHash($payload, $algorithm, $contentType)
    {
        list ($contentType) = explode(';', $contentType);
        $contentType = strtolower(trim($contentType));

        $normalized = 'hawk.' . self::HEADER_VERSION . '.payload' . "\n" .
            $contentType . "\n" .
            $payload . "\n";

        return base64_encode(hash($algorithm, $normalized, true));
    }

    /**
     * @param string $type
     * @param CredentialsInterface $credentials
     * @param Artifacts $attributes
     * @return string
     */
    public function calculateMac($type, CredentialsInterface $credentials, Artifacts $attributes)
    {
        $normalized = $this->generateNormalizedString($type, $attributes);

        return base64_encode(hash_hmac($credentials->algorithm(), $normalized, $credentials->key(), true));
    }

    /**
     * @param int $ts
     * @param CredentialsInterface $credentials
     * @return string
     */
    public function calculateTsMac($ts, CredentialsInterface $credentials)
    {
        $normalized = 'hawk.' . self::HEADER_VERSION . '.ts' . "\n" .
            $ts . "\n";

        return base64_encode(hash_hmac(
            $credentials->algorithm(),
            $normalized,
            $credentials->key(),
            true
        ));
    }

    /**
     * @param int $a
     * @param int $b
     * @return bool
     */
    public function fixedTimeComparison($a, $b)
    {
        $mismatch = strlen($a) === strlen($b) ? 0 : 1;
        if ($mismatch) {
            $b = $a;
        }

        for ($i = 0; $i < strlen($a); $i++) {
            $ac = $a[$i];
            $bc = $b[$i];
            $mismatch += $ac === $bc ? 0 : 1;
        }

        return (0 === $mismatch);
    }

    /**
     * @param string $type
     * @param Artifacts $attributes
     * @return string
     */
    private function generateNormalizedString($type, Artifacts $attributes)
    {
        $normalized = 'hawk.' . self::HEADER_VERSION . '.' . $type . "\n" .
            $attributes->timestamp() . "\n" .
            $attributes->nonce() . "\n" .
            strtoupper($attributes->method()) . "\n" .
            $attributes->resource() . "\n" .
            strtolower($attributes->host()) . "\n" .
            $attributes->port() . "\n" .
            $attributes->hash() . "\n";

        if ($attributes->ext()) {
            // TODO: escape ext
            $normalized .= $attributes->ext();
        }

        $normalized .= "\n";

        if ($attributes->app()) {
            $normalized .= $attributes->app() . "\n" .
                $attributes->dlg() . "\n";
        }

        return $normalized;
    }
}
