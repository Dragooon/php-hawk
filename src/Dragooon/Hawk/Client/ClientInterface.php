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
     * @param $headerObjectOrString
     * @param array $options
     * @return mixed
     */
    public function authenticate(
        CredentialsInterface $credentials,
        Request $request,
        $headerObjectOrString,
        array $options = array()
    );
}
