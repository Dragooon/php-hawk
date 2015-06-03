<?php

namespace Dragooon\Hawk\Nonce;

use RandomLib\Factory;

class DefaultNonceProviderFactory
{
    /**
     * @return NonceProvider
     */
    public static function create()
    {
        $factory = new Factory;

        return new NonceProvider($factory->getLowStrengthGenerator());
    }
}
