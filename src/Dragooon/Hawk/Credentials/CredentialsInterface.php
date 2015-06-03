<?php

namespace Dragooon\Hawk\Credentials;

interface CredentialsInterface
{
    public function key();
    public function algorithm();
    public function id();
}
