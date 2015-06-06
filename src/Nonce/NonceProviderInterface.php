<?php

namespace Dragooon\Hawk\Nonce;

interface NonceProviderInterface
{
    /**
     * @return string
     */
    public function createNonce();
}
