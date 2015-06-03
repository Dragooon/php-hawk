<?php

namespace Dragooon\Hawk\Credentials;

class Credentials implements CredentialsInterface
{
    private $key;
    private $algorithm;
    private $id;

    /**
     * @param $key
     * @param string $algorithm
     * @param mixed $id
     */
    public function __construct($key, $algorithm = 'sha256', $id = null)
    {
        $this->key = $key;
        $this->algorithm = $algorithm;
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function algorithm()
    {
        return $this->algorithm;
    }
}
