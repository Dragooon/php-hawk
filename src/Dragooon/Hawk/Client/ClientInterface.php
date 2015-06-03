<?php

namespace Dragooon\Hawk\Client;

use Dragooon\Hawk\Credentials\CredentialsInterface;

interface ClientInterface
{
    public function createRequest(CredentialsInterface $credentials, $uri, $method, array $options = array());
    public function authenticate(
        CredentialsInterface $credentials,
        Request $request,
        $headerObjectOrString,
        array $options = array()
    );
}
