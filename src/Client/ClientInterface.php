<?php

namespace Dragooon\Hawk\Client;

use Dragooon\Hawk\Credentials\CredentialsInterface;

interface ClientInterface
{
    /**
     * @param CredentialsInterface $credentials
     * @param $uri
     * @param $method
     * @param array $options
     * @return mixed
     */
    public function createRequest(CredentialsInterface $credentials, $uri, $method, array $options = array());

    /**
     * @param CredentialsInterface $credentials
     * @param Request $request
     * @param mixed $headerObjectOrString Response header
     * @param array $options
     * @return mixed
     */
    public function authenticate(
        CredentialsInterface $credentials,
        Request $request,
        $headerObjectOrString,
        array $options = array()
    );

    /**
     * @param CredentialsInterface $credentials
     * @param $uri
     * @param $ttlSec
     * @param array $options
     * @return mixed
     */
    public function createBewit(CredentialsInterface $credentials, $uri, $ttlSec, array $options = array());


    /**
     * Generate an authorization string for a message
     *
     * @param CredentialsInterface $credentials
     * @param string $host
     * @param int $port
     * @param string $message
     * @param array $options
     * @return \Dragooon\Hawk\Client\Message
     * @throws \InvalidArgumentException
     */
    public function createMessage(CredentialsInterface $credentials, $host, $port, $message, array $options = array());

}
