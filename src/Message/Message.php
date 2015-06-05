<?php

namespace Dragooon\Hawk\Message;

class Message
{
    protected $id;
    protected $timestamp;
    protected $nonce;
    protected $hash;
    protected $mac;

    /**
     * @param mixed $id
     * @param int $timestamp
     * @param mixed $nonce
     * @param string $hash
     * @param string $mac
     */
    public function __construct($id, $timestamp, $nonce, $hash, $mac)
    {
        $this->id = $id;
        $this->timestamp = $timestamp;
        $this->nonce = $nonce;
        $this->hash = $hash;
        $this->mac = $mac;
    }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function timestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return mixed
     */
    public function nonce()
    {
        return $this->nonce;
    }

    /**
     * @return string
     */
    public function hash()
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function mac()
    {
        return $this->mac;
    }
}
