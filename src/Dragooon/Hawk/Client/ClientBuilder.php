<?php

namespace Dragooon\Hawk\Client;

use Dragooon\Hawk\Crypto\Crypto;
use Dragooon\Hawk\Nonce\DefaultNonceProviderFactory;
use Dragooon\Hawk\Nonce\NonceProviderInterface;
use Dragooon\Hawk\Time\DefaultTimeProviderFactory;
use Dragooon\Hawk\Time\TimeProviderInterface;

class ClientBuilder
{
    private $crypto;
    private $timeProvider;
    private $nonceProvider;
    private $localtimeOffset = 0;

    /**
     * @param Crypto $crypto
     * @return $this
     */
    public function setCrypto(Crypto $crypto)
    {
        $this->crypto = $crypto;

        return $this;
    }

    /**
     * @param TimeProviderInterface $timeProvider
     * @return $this
     */
    public function setTimeProvider(TimeProviderInterface $timeProvider)
    {
        $this->timeProvider = $timeProvider;

        return $this;
    }

    /**
     * @param NonceProviderInterface $nonceProvider
     * @return $this
     */
    public function setNonceProvider(NonceProviderInterface $nonceProvider)
    {
        $this->nonceProvider = $nonceProvider;

        return $this;
    }

    /**
     * @param null $localtimeOffset
     * @return $this
     */
    public function setLocaltimeOffset($localtimeOffset = null)
    {
        $this->localtimeOffset = $localtimeOffset;

        return $this;
    }

    /**
     * @return Client
     */
    public function build()
    {
        $crypto = $this->crypto ?: new Crypto;
        $timeProvider = $this->timeProvider ?: DefaultTimeProviderFactory::create();
        $nonceProvider = $this->nonceProvider ?: DefaultNonceProviderFactory::create();

        return new Client(
            $crypto,
            $timeProvider,
            $nonceProvider,
            $this->localtimeOffset
        );
    }

    /**
     * @return static
     */
    public static function create()
    {
        return new static;
    }
}
