<?php

namespace Dragooon\Hawk\Header;

class HeaderParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldTestNotHawkAuthorizationException()
    {
        try {
            HeaderParser::parseFieldValue('TestHeader mac="test"');
            $this->fail('Should throw an exception for invalid header');
        } catch (NotHawkAuthorizationException $e) {
        }

        return true;
    }

    /**
     * @test
     */
    public function shouldTestFieldValueParserException()
    {
        try {
            $header = 'Hawk id="123456", ts="1353809207", hash="bsvY3IfUllw6V5rvk4tStEvpBhE=", ext="Bazinga!", mac="qbf1ZPG/r/e06F4ht+T77LXi5vw="';
            HeaderParser::parseFieldValue($header, ['id', 'nonce', 'hash']);
            $this->fail('Should throw an exception for missing required values');
        } catch (FieldValueParserException $e) {
        }

        return true;
    }

    /**
     * @test
     */
    public function shouldTestParseFieldValue()
    {
        $header = 'Hawk id="123456", ts="1353809207", nonce="Ygvqdz", hash="bsvY3IfUllw6V5rvk4tStEvpBhE=", ext="Bazinga!", mac="qbf1ZPG/r/e06F4ht+T77LXi5vw="';
        $header = HeaderParser::parseFieldValue($header, ['id', 'ts']);

        $this->assertTrue(is_array($header));
        $this->assertEquals(123456, $header['id']);
        $this->assertEquals(1353809207, $header['ts']);

        try {
            $header = 'Hawk wrong="test"';
            HeaderParser::parseFieldValue($header);
            $this->fail('Should throw an exception for invalid key');
        } catch (FieldValueParserException $e) {
            $this->assertEquals('Invalid key: wrong', $e->getMessage());
        }
    }
}