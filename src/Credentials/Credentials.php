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
     * @throws \InvalidArgumentException
     */
    public function __construct($key, $algorithm = 'sha256', $id = null)
    {
        if (!in_array($algorithm, hash_algos())) {
            throw new \InvalidArgumentException('Invalid hash specified');
        }

        $this->key = trim($key);
        $this->algorithm = trim($algorithm);
        $this->id = trim($id);
    }

    /**
     * {@inheritDoc}
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritDoc}
     */
    public function algorithm()
    {
        return $this->algorithm;
    }
}
