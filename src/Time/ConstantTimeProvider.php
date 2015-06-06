<?php

namespace Dragooon\Hawk\Time;

class ConstantTimeProvider implements TimeProviderInterface
{
    private $time;

    /**
     * @param int $time
     */
    public function __construct($time)
    {
        $this->time = $time;
    }

    /**
     * {@inheritDoc}
     */
    public function createTimestamp()
    {
        return $this->time;
    }
}
