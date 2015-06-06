<?php

namespace Dragooon\Hawk\Credentials;

interface CredentialsProviderInterface
{
    /**
     * @param mixed $id
     * @return Credentials
     * @throws CredentialsNotFoundException
     */
    public function loadCredentialsById($id);
}
