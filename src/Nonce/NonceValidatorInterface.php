<?php

namespace Dragooon\Hawk\Nonce;

interface NonceValidatorInterface
{
    /**
     * @param string $key
     * @param string $nonce
     * @param int $timestamp
     * @return bool
     */
    public function validateNonce($key, $nonce, $timestamp);
}
