<?php

namespace Dragooon\Hawk\Crypto;

class Artifacts
{
    private $method;
    private $host;
    private $port;
    private $resource;
    private $timestamp;
    private $nonce;
    private $ext;
    private $payload;
    private $contentType;
    private $hash;
    private $app;
    private $dlg;

    /**
     * @param string $method
     * @param string $host
     * @param int $port
     * @param string $resource
     * @param int $timestamp
     * @param string $nonce
     * @param string $ext
     * @param string $payload
     * @param string $contentType
     * @param string $hash
     * @param string $app
     * @param string $dlg
     */
    public function __construct(
        $method,
        $host,
        $port,
        $resource,
        $timestamp,
        $nonce,
        $ext = null,
        $payload = null,
        $contentType = null,
        $hash = null,
        $app = null,
        $dlg = null
    )
    {
        $this->method = $method;
        $this->host = $host;
        $this->port = $port;
        $this->resource = $resource;
        $this->timestamp = $timestamp;
        $this->nonce = $nonce;
        $this->ext = $ext;
        $this->payload = $payload;
        $this->contentType = $contentType;
        $this->hash = $hash;
        $this->app = $app;
        $this->dlg = $dlg;
    }

    /**
     * @return int
     */
    public function timestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function nonce()
    {
        return $this->nonce;
    }

    /**
     * @return null|string
     */
    public function ext()
    {
        return $this->ext;
    }

    /**
     * @return null|string
     */
    public function payload()
    {
        return $this->payload;
    }

    /**
     * @return null|string
     */
    public function contentType()
    {
        return $this->contentType;
    }

    /**
     * @return null|string
     */
    public function hash()
    {
        return $this->hash;
    }

    /**
     * @return null|string
     */
    public function app()
    {
        return $this->app;
    }

    /**
     * @return null|string
     */
    public function dlg()
    {
        return $this->dlg;
    }

    /**
     * @return string
     */
    public function resource()
    {
        return $this->resource;
    }

    /**
     * @return string
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function port()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function method()
    {
        return $this->method;
    }
}
