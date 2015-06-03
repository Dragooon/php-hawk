<?php

namespace Dragooon\Hawk\Time;

class TimeProvider implements TimeProviderInterface
{
    /**
     * @return int
     */
    public function createTimestamp()
    {
        return time();
    }
}
