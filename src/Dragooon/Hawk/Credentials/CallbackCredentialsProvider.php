<?php

namespace Dragooon\Hawk\Credentials;

class CallbackCredentialsProvider implements CredentialsProviderInterface
{
    private $callback;

    /**
     * @param $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function loadCredentialsById($id)
    {
        return call_user_func($this->callback, $id);
    }
}
