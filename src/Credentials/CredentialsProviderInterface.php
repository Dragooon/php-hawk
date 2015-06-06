<?php

namespace Dragooon\Hawk\Credentials;

interface CredentialsProviderInterface
{
    /**
     * @param $id
     * @return mixed
     * @throws CredentialsNotFoundException
     */
    public function loadCredentialsById($id);
}
