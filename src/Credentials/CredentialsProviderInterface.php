<?php

namespace Dragooon\Hawk\Credentials;

interface CredentialsProviderInterface
{
    /**
     * @param $id
     * @return mixed
     */
    public function loadCredentialsById($id);
}
