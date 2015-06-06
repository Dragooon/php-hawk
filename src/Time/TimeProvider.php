<?php

namespace Dragooon\Hawk\Time;

class TimeProvider implements TimeProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function createTimestamp()
    {
        return time();
    }
}
