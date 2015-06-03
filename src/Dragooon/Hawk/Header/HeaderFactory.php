<?php

namespace Dragooon\Hawk\Header;

class HeaderFactory
{
    /**
     * @param string $fieldName
     * @param array $attributes
     * @return Header
     */
    public static function create($fieldName, array $attributes = null)
    {
        $fieldValue = 'Hawk';

        if (null !== $attributes) {
            $index = 0;
            foreach ($attributes as $key => $value) {
                if ($index++ > 0) {
                    $fieldValue .= ',';
                }

                $fieldValue .= ' '.$key.'="'.$value.'"';
            }
        }

        return new Header($fieldName, $fieldValue, $attributes);
    }

    /**
     * @param string $fieldName
     * @param mixed $fieldValue
     * @param array $requiredKeys
     * @return Header
     * @throws FieldValueParserException
     * @throws NotHawkAuthorizationException
     */
    public static function createFromString($fieldName, $fieldValue, array $requiredKeys = null)
    {
        return static::create(
            $fieldName,
            HeaderParser::parseFieldValue($fieldValue, $requiredKeys)
        );
    }

    /**
     * @param string $fieldName
     * @param mixed $headerObjectOrString
     * @param callback $onError
     * @return Header
     */
    public static function createFromHeaderObjectOrString($fieldName, $headerObjectOrString, $onError)
    {
        if (is_string($headerObjectOrString)) {
            return static::createFromString($fieldName, $headerObjectOrString);
        } elseif ($headerObjectOrString instanceof Header) {
            return $headerObjectOrString;
        } else {
            call_user_func($onError);
        }
    }
}
