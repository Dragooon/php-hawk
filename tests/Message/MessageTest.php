<?php

namespace Dragooon\Hawk\Message;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldTestSerialization()
    {
        $message = new Message(123456, 1433581607, 'abc123', 'hash1233', 'mac1234');
        $serialized = $message->serialized();

        $this->assertEquals('{"id":"123456","timestamp":"1433581607","nonce":"abc123","hash":"hash1233","mac":"mac1234"}', $serialized, 'Should generate JSON encoded object');

        $unserialized = Message::createFromSerialized($serialized);
        $this->assertEquals('abc123', $unserialized->nonce());
        $this->assertEquals(123456, $unserialized->id());
    }
}