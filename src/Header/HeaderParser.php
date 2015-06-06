<?php

namespace Dragooon\Hawk\Header;

class HeaderParser
{
    /**
     * @param mixed $fieldValue
     * @param array $requiredKeys
     * @param array $validKeys
     * @return array
     * @throws FieldValueParserException
     * @throws NotHawkAuthorizationException
     */
    public static function parseFieldValue($fieldValue, array $requiredKeys = null, array $validKeys = array())
    {
        if (0 !== strpos($fieldValue, 'Hawk')) {
            throw new NotHawkAuthorizationException;
        }

        if (empty($validKeys)) {
            $validKeys = ['id', 'ts', 'nonce', 'hash', 'ext', 'mac', 'app', 'dlg'];
        }

        $attributes = [];
        $fieldValue = substr($fieldValue, 5);
        foreach (explode(', ', $fieldValue) as $part) {
            $equalsPos = strpos($part, '=');
            $key = trim(substr($part, 0, $equalsPos));
            $value = trim(trim(substr($part, $equalsPos + 1), '"'));
            $attributes[$key] = $value;

            if (empty($key)) {
                continue;
            }

            if (!in_array($key, $validKeys)) {
                throw new FieldValueParserException('Invalid key: ' . $key);
            }
        }

        if (null !== $requiredKeys) {
            $missingKeys = [];
            foreach ($requiredKeys as $requiredKey) {
                if (!isset($attributes[$requiredKey])) {
                    $missingKeys[] = $requiredKey;
                }
            }

            if (count($missingKeys)) {
                throw new FieldValueParserException(
                    "Field value was missing the following required key(s): " . implode(', ', $missingKeys)
                );
            }
        }

        return $attributes;
    }
}
