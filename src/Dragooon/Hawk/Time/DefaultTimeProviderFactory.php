<?php

namespace Dragooon\Hawk\Time;

class DefaultTimeProviderFactory
{
    /**
     * @return TimeProvider
     */
    public static function create()
    {
        return new TimeProvider;
    }
}
