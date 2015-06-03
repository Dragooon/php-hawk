<?php

namespace Dragooon\Hawk\Time;

class TimeProvider implements TimeProviderInterface
{
    public function createTimestamp()
    {
        return time();
    }
}
