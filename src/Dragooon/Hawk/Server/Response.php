<?php

namespace Dragooon\Hawk\Server;

use Dragooon\Hawk\Credentials\CredentialsInterface;
use Dragooon\Hawk\Crypto\Artifacts;

class Response
{
    private $credentials;
    private $artifacts;

    public function __construct(CredentialsInterface $credentials, Artifacts $artifacts)
    {
        $this->credentials = $credentials;
        $this->artifacts = $artifacts;
    }

    public function credentials()
    {
        return $this->credentials;
    }

    public function artifacts()
    {
        return $this->artifacts;
    }
}
