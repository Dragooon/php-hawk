<?php

namespace Dragooon\Hawk\Server;

use Dragooon\Hawk\Credentials\CredentialsInterface;
use Dragooon\Hawk\Crypto\Artifacts;

interface ServerInterface
{
    /**
     * @param string $method
     * @param string $host
     * @param int $port
     * @param mixed $resource
     * @param string $contentType
     * @param string $payload
     * @param mixed $headerObjectOrString
     * @return mixed
     */
    public function authenticate(
        $method,
        $host,
        $port,
        $resource,
        $contentType = null,
        $payload = null,
        $headerObjectOrString = null
    );

    /**
     * @param CredentialsInterface $credentials
     * @param Artifacts $artifacts
     * @param array $options
     * @return mixed
     */
    public function createHeader(CredentialsInterface $credentials, Artifacts $artifacts, array $options = array());

    /**
     * @param CredentialsInterface $credentials
     * @param string $payload
     * @param string $contentType
     * @param string $hash
     * @return mixed
     */
    public function authenticatePayload(
        CredentialsInterface $credentials,
        $payload,
        $contentType,
        $hash
    );
}
