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
        $this->id = trim($id);
        $this->timestamp = trim($timestamp);
        $this->nonce = trim($nonce);
        $this->hash = trim($hash);
        $this->mac = trim($mac);
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

    /**
     * Generates a serialized (json_encoded) version of the message for transport
     *
     * @return string
     */
    public function serialized()
    {
        return json_encode(
            [
                'id' => $this->id,
                'timestamp' => $this->timestamp,
                'nonce' => $this->nonce,
                'hash' => $this->hash,
                'mac' => $this->mac,
            ]
        );
    }

    /**
     * Takes a serialized (json_encoded) string and returns it's Message object
     *
     * @param string $serialized
     * @return Message
     * @throws \InvalidArgumentException
     */
    public static function createFromSerialized($serialized)
    {
        $unserialized = json_decode($serialized, true);
        if (!$unserialized || empty($unserialized['id']) || empty($unserialized['timestamp']) || empty($unserialized['nonce'])
            || empty($unserialized['hash']) || empty($unserialized['mac'])
        ) {
            throw new \InvalidArgumentException('Invalid serialized string passed');
        }

        return new Message(
            $unserialized['id'],
            $unserialized['timestamp'],
            $unserialized['nonce'],
            $unserialized['hash'],
            $unserialized['mac']
        );
    }
}
