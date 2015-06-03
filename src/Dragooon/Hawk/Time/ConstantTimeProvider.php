<?php

namespace Dragooon\Hawk\Time;

class ConstantTimeProvider implements TimeProviderInterface
{
    private $time;

    public function __construct($time)
    {
        $this->time = $time;
    }

    public function createTimestamp()
    {
        return $this->time;
    }
}
