<?php

namespace Dragooon\Hawk\Credentials;

interface CredentialsProviderInterface
{
    public function loadCredentialsById($id);
}
