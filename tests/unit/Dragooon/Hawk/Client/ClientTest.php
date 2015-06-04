<?php

namespace Dragooon\Hawk\Client;

use Dragooon\Hawk\Credentials\Credentials;
use Dragooon\Hawk\Nonce\NonceProviderInterface;
use Dragooon\Hawk\Time\TimeProviderInterface;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateBewit()
    {
        $client = ClientBuilder::create()->build();

        $tentTestVectorsCredentials = new Credentials(
            'HX9QcbD-r3ItFEnRcAuOSg',
            'sha256',
            'exqbZWtykFZIh2D7cXi9dA'
        );

        $this->assertEquals(
            'ZXhxYlpXdHlrRlpJaDJEN2NYaTlkQVwxMzY4OTk2ODAwXE8wbWhwcmdvWHFGNDhEbHc1RldBV3ZWUUlwZ0dZc3FzWDc2dHBvNkt5cUk9XA',
            $client->createBewit(
                $tentTestVectorsCredentials,
                'https://example.com/posts',
                0,
                array(
                    'timestamp' => 1368996800,
                )
            )
        );
    }

    /**
     * @test
     * @dataProvider headerDataProvider
     *
     * @param Credentials $credentials
     * @param string $url
     * @param string $method
     * @param array $options
     * @param mixed $expectedHeader False if the header is expected to throw an exception
     * @param string $message
     * @return void
     */
    public function shouldTestHeader(Credentials $credentials, $url, $method, array $options, $expectedHeader, $message)
    {
        $client = ClientBuilder::create()->build();

        if ($expectedHeader === false) {
            $this->setExpectedException('InvalidArgumentException');
        }

        $header = $client->createRequest($credentials, $url, $method, $options)->header();

        $this->assertEquals($expectedHeader, $header->fieldValue(), $message);
    }

    /**
     * @return array
     */
    public function headerDataProvider()
    {
        return array(
            array(
                new Credentials('2983d45yun89q', 'sha1', 123456),
                'http://example.net/somewhere/over/the/rainbow',
                'POST',
                array('ext' => 'Bazinga!', 'timestamp' => 1353809207, 'nonce' => 'Ygvqdz', 'payload' => 'something to write about'),
                'Hawk id="123456", ts="1353809207", nonce="Ygvqdz", hash="bsvY3IfUllw6V5rvk4tStEvpBhE=", ext="Bazinga!", mac="qbf1ZPG/r/e06F4ht+T77LXi5vw="',
                'Header with sha1 hash should be equal'
            ),
            array(
                new Credentials('2983d45yun89q', 'sha256', 123456),
                'https://example.net/somewhere/over/the/rainbow',
                'POST',
                array('ext' => 'Bazinga!', 'timestamp' => 1353809207, 'nonce' => 'Ygvqdz', 'payload' => 'something to write about', 'content_type' => 'text/plain'),
                'Hawk id="123456", ts="1353809207", nonce="Ygvqdz", hash="2QfCt3GuY9HQnHWyWD3wX68ZOKbynqlfYmuO2ZBRqtY=", ext="Bazinga!", mac="q1CwFoSHzPZSkbIvl0oYlD+91rBUEvFk763nMjMndj8="',
                'Header with sha256 hash should be equal'
            ),
            array(
                new Credentials('2983d45yun89q', 'sha256', 123456),
                'https://example.net/somewhere/over/the/rainbow',
                'POST',
                array('timestamp' => 1353809207, 'nonce' => 'Ygvqdz', 'payload' => 'something to write about', 'content_type' => 'text/plain'),
                'Hawk id="123456", ts="1353809207", nonce="Ygvqdz", hash="2QfCt3GuY9HQnHWyWD3wX68ZOKbynqlfYmuO2ZBRqtY=", mac="HTgtd0jPI6E4izx8e4OHdO36q00xFCU0FolNq3RiCYs="',
                'Header with sha256 hash should be equal (with no ext)'
            ),
            array(
                new Credentials('2983d45yun89q', 'sha256', 123456),
                'https://example.net/somewhere/over/the/rainbow',
                'POST',
                array('ext' => null, 'timestamp' => 1353809207, 'nonce' => 'Ygvqdz', 'payload' => 'something to write about', 'content_type' => 'text/plain'),
                'Hawk id="123456", ts="1353809207", nonce="Ygvqdz", hash="2QfCt3GuY9HQnHWyWD3wX68ZOKbynqlfYmuO2ZBRqtY=", mac="HTgtd0jPI6E4izx8e4OHdO36q00xFCU0FolNq3RiCYs="',
                'Header with sha256 hash should be equal (ext specified as null)'
            ),
            array(
                new Credentials('2983d45yun89q', 'sha256', 123456),
                'https://example.net/somewhere/over/the/rainbow',
                'POST',
                array('timestamp' => 1353809207, 'nonce' => 'Ygvqdz', 'payload' => '', 'content_type' => 'text/plain'),
                'Hawk id="123456", ts="1353809207", nonce="Ygvqdz", hash="q/t+NNAkQZNlq/aAD6PlexImwQTxwgT2MahfTa9XRLA=", mac="U5k16YEzn3UnBHKeBzsDXn067Gu3R4YaY6xOt9PYRZM="',
                'Header with sha256 hash should be equal (empty payload)'
            ),
            array(
                new Credentials('2983d45yun89q', 'sha256', 123456),
                '',
                'POST',
                array('timestamp' => 1353809207, 'nonce' => 'Ygvqdz', 'payload' => '', 'content_type' => 'text/plain'),
                false,
                'Header should return an error (missing URI)'
            ),
            array(
                new Credentials('2983d45yun89q', 'sha256', 123456),
                4,
                'POST',
                array('timestamp' => 1353809207, 'nonce' => 'Ygvqdz', 'payload' => '', 'content_type' => 'text/plain'),
                false,
                'Header should return an error (invalid URI)'
            ),
            array(
                new Credentials('2983d45yun89q', 'sha256', 123456),
                'https://example.net/somewhere/over/the/rainbow',
                '',
                array('timestamp' => 1353809207, 'nonce' => 'Ygvqdz', 'payload' => '', 'content_type' => 'text/plain'),
                false,
                'Header should return an error (missing method)'
            ),
            array(
                new Credentials('2983d45yun89q', 'sha256', 123456),
                'https://example.net/somewhere/over/the/rainbow',
                4,
                array('timestamp' => 1353809207, 'nonce' => 'Ygvqdz', 'payload' => '', 'content_type' => 'text/plain'),
                false,
                'Header should return an error (invalid method)'
            ),
            array(
                new Credentials('2983d45yun89q', 'sha256'),
                'https://example.net/somewhere/over/the/rainbow',
                'POST',
                array('timestamp' => 1353809207, 'nonce' => 'Ygvqdz', 'payload' => '', 'content_type' => 'text/plain'),
                false,
                'Header should return an error (invalid credentials)'
            ),
        );
    }

    /**
     * @test
     * @dataProvider messageDataProvider
     *
     * @param Credentials $credentials
     * @param string $host
     * @param int $port
     * @param string $message
     * @param array $options
     * @param mixed $expected
     * @param string $testMessage
     */
    public function shouldTestMessage(Credentials $credentials, $host, $port, $message, array $options, $expected, $testMessage)
    {
        $client = ClientBuilder::create()->build();

        if ($expected === false) {
            $this->setExpectedException('InvalidArgumentException');
        }

        $message = $client->createMessage($credentials, $host, $port, $message, $options);

        if (!empty($expected)) {
            $this->assertEquals($options['timestamp'], $message->timestamp(), $testMessage);
            $this->assertEquals($options['nonce'], $message->nonce(), $testMessage);
            $this->assertEquals($expected, $message->mac(), $testMessage);
        }
    }

    /**
     * @return array
     */
    public function messageDataProvider()
    {
        return array(
            array(
                new Credentials('2983d45yun89q', 'sha256', 123456),
                'example.net',
                80,
                'I am the boodyman',
                array('timestamp' => 1353809207, 'nonce' => 'abc123'),
                'fWpeQac+YUDgpFkOXiJCfHXV19FHU6uKJh2pXyKa8BQ=',
                'Message authorization should be generated'
            ),
            array(
                new Credentials('2983d45yun89q', 'sha256'),
                'example.net',
                80,
                'I am the boodyman',
                array('timestamp' => 1353809207, 'nonce' => 'abc123'),
                false,
                'Message authorization should fail on invalid credentials'
            ),
            array(
                new Credentials('2983d45yun89q', 'sha256', 123456),
                '',
                80,
                'I am the boodyman',
                array('timestamp' => 1353809207, 'nonce' => 'abc123'),
                false,
                'Message authorization should fail on invalid host'
            ),
        );
    }
}
