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
     * {@inheritDoc}
     */
    public function loadCredentialsById($id)
    {
        $result = call_user_func($this->callback, $id);

        if (empty($result)) {
            throw new CredentialsNotFoundException($id);
        }

        return $result;
    }
}
