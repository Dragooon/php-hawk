<?php

namespace Dragooon\Hawk\Server;

use Dragooon\Hawk\Credentials\CredentialsInterface;
use Dragooon\Hawk\Crypto\Artifacts;

interface ServerInterface
{
    public function authenticate(
        $method,
        $host,
        $port,
        $resource,
        $contentType = null,
        $payload = null,
        $headerObjectOrString = null
    );
    public function createHeader(CredentialsInterface $credentials, Artifacts $artifacts, array $options = array());
    public function authenticatePayload(
        CredentialsInterface $credentials,
        $payload,
        $contentType,
        $hash
    );
}
