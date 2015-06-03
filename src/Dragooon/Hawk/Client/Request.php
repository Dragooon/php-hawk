<?php

namespace Dragooon\Hawk\Client;

use Dragooon\Hawk\Crypto\Artifacts;
use Dragooon\Hawk\Header\Header;

class Request
{
    private $header;
    private $artifacts;

    public function __construct(Header $header, Artifacts $artifacts)
    {
        $this->header = $header;
        $this->artifacts = $artifacts;
    }

    public function header()
    {
        return $this->header;
    }

    public function artifacts()
    {
        return $this->artifacts;
    }
}
