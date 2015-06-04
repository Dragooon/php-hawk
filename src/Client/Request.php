<?php

namespace Dragooon\Hawk\Client;

use Dragooon\Hawk\Crypto\Artifacts;
use Dragooon\Hawk\Header\Header;

class Request
{
    private $header;
    private $artifacts;

    /**
     * @param Header $header
     * @param Artifacts $artifacts
     */
    public function __construct(Header $header, Artifacts $artifacts)
    {
        $this->header = $header;
        $this->artifacts = $artifacts;
    }

    /**
     * @return Header
     */
    public function header()
    {
        return $this->header;
    }

    /**
     * @return Artifacts
     */
    public function artifacts()
    {
        return $this->artifacts;
    }
}
