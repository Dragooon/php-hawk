<?php

namespace Dragooon\Hawk\Server;

use Dragooon\Hawk\Credentials\CredentialsInterface;
use Dragooon\Hawk\Crypto\Artifacts;
use Dragooon\Hawk\Header\Header;
use Dragooon\Hawk\Message\Message;

interface ServerInterface
{
    /**
     * @param string $method
     * @param string $host
     * @param int $port
     * @param mixed $resource
     * @param string $contentType
     * @param string $payload
     * @param Header|string $headerObjectOrString
     * @return Response
     * @throws UnauthorizedException
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
     * @return Header
     * @throws \InvalidArgumentException
     */
    public function createHeader(CredentialsInterface $credentials, Artifacts $artifacts, array $options = []);

    /**
     * @param CredentialsInterface $credentials
     * @param string $payload
     * @param string $contentType
     * @param string $hash
     * @return bool
     */
    public function authenticatePayload(
        CredentialsInterface $credentials,
        $payload,
        $contentType,
        $hash
    );

    /**
     * @param string $host
     * @param int $port
     * @param string $resource
     * @return Response
     * @throws UnauthorizedException
     */
    public function authenticateBewit($host, $port, $resource);

    /**
     * Authenticates a single message from a client
     *
     * @param string $host
     * @param int $port
     * @param string $message
     * @param Message $authorization
     * @return Response
     * @throws UnauthorizedException
     */
    public function authenticateMessage($host, $port, $message, Message $authorization);
}
