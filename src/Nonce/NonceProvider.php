<?php

namespace Dragooon\Hawk\Nonce;

use RandomLib\Generator;

class NonceProvider implements NonceProviderInterface
{
    private $generator;

    /**
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * {@inheritDoc}
     */
    public function createNonce()
    {
        return $this->generator->generateString(
            32,
            'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
        );
    }
}
