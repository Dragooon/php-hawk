<?php

namespace Dragooon\Hawk\Header;

class NotHawkAuthorizationException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Field value does not start with Hawk");
    }
}
