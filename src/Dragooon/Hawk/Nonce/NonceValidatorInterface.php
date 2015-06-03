<?php

namespace Dragooon\Hawk\Nonce;

interface NonceValidatorInterface
{
    public function validateNonce($nonce, $timestamp);
}
