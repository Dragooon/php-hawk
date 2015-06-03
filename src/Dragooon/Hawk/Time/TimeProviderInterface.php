<?php

namespace Dragooon\Hawk\Time;

interface TimeProviderInterface
{
    /**
     * @return int
     */
    public function createTimestamp();
}
