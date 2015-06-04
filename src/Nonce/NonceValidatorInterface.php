<?php

namespace Dragooon\Hawk\Nonce;

interface NonceValidatorInterface
{
    /**
     * @param string $nonce
     * @param int $timestamp
     * @return mixed
     */
    public function validateNonce($nonce, $timestamp);
}
