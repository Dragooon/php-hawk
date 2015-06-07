<?php

namespace Dragooon\Hawk\Credentials;

interface CredentialsInterface
{
    /**
     * Encryption key for the credential this is referring to
     *
     * @return mixed
     */
    public function key();

    /**
     * Hashing algorithm the client is expected to use, example: sha1, sha256 etc
     *
     * @return mixed
     */
    public function algorithm();

    /**
     * ID, not required in all cases
     *
     * @return mixed
     */
    public function id();
}
