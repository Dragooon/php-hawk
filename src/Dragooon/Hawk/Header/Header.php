<?php

namespace Dragooon\Hawk\Header;

class Header
{
    private $fieldName;
    private $fieldValue;
    private $attributes;

    /**
     * @param string  $fieldName
     * @param string $fieldValue
     * @param array $attributes
     */
    public function __construct($fieldName, $fieldValue, array $attributes = null)
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
        $this->attributes = $attributes ?: array();
    }

    /**
     * @return string
     */
    public function fieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function fieldValue()
    {
        return $this->fieldValue;
    }

    /**
     * @param array $keys
     * @return array
     */
    public function attributes(array $keys = null)
    {
        if (null === $keys) {
            return $this->attributes;
        }

        $attributes = array();
        foreach ($keys as $key) {
            if (isset($this->attributes[$key])) {
                $attributes[$key] = $this->attributes[$key];
            }
        }

        return $attributes;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function attribute($key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }
    }
}
