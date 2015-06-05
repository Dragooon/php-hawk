<?php

namespace Dragooon\Hawk\Credentials;

use Dragooon\Hawk\Credentials\CredentialsNotFoundException;

interface CredentialsProviderInterface
{
    /**
     * @param $id
     * @return mixed
     * @throws CredentialsNotFoundException
     */
    public function loadCredentialsById($id);
}
