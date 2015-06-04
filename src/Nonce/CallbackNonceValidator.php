<?php

namespace Dragooon\Hawk\Nonce;

class CallbackNonceValidator implements NonceValidatorInterface
{
    private $callback;

    /**
     * @param callback $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param mixed $nonce
     * @param int $timestamp
     * @return mixed
     */
    public function validateNonce($nonce, $timestamp)
    {
        return call_user_func_array($this->callback, array($nonce, $timestamp));
    }
}
