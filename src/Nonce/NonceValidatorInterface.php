<?php

namespace Dragooon\Hawk\Nonce;

interface NonceValidatorInterface
{
    /**
     * @param string $nonce
     * @param int $timestamp
     * @return bool
     */
    public function validateNonce($nonce, $timestamp);
}
