<?php

namespace Dragooon\Hawk\Time;

interface TimeProviderInterface
{
    /**
     * Returns an unix timestamp
     * 
     * @return int
     */
    public function createTimestamp();
}
