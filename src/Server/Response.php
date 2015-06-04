<?php

namespace Dragooon\Hawk\Server;

use Dragooon\Hawk\Credentials\CredentialsInterface;
use Dragooon\Hawk\Crypto\Artifacts;

class Response
{
    private $credentials;
    private $artifacts;

    /**
     * @param CredentialsInterface $credentials
     * @param Artifacts $artifacts
     */
    public function __construct(CredentialsInterface $credentials, Artifacts $artifacts)
    {
        $this->credentials = $credentials;
        $this->artifacts = $artifacts;
    }

    /**
     * @return CredentialsInterface
     */
    public function credentials()
    {
        return $this->credentials;
    }

    /**
     * @return Artifacts
     */
    public function artifacts()
    {
        return $this->artifacts;
    }
}
