<?php

namespace Dragooon\Hawk\Credentials;

interface CredentialsInterface
{
    /**
     * @return mixed
     */
    public function key();

    /**
     * @return mixed
     */
    public function algorithm();

    /**
     * @return mixed
     */
    public function id();
}
