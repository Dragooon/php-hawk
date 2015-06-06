<?php

namespace Dragooon\Hawk\Server;

use Dragooon\Hawk\Credentials\Credentials;
use Dragooon\Hawk\Credentials\CallbackCredentialsProvider;
use Dragooon\Hawk\Crypto\Artifacts;
use Dragooon\Hawk\Nonce\CallbackNonceValidator;
use Dragooon\Hawk\Time\DefaultTimeProviderFactory;
use Dragooon\Hawk\Message\Message;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider bewitDataProvider
     *
     * @param string $host
     * @param int $port
     * @param string $resource
     * @param int $localTimeOffsetSec
     * @param string $expected
     * @param string $message
     */
    public function shouldAuthenticateBewit($host, $port, $resource, $localTimeOffsetSec, $expected, $message)
    {
        $key = 'HX9QcbD-r3ItFEnRcAuOSg';
        $credentialsProvider = new CallbackCredentialsProvider(
            function ($id) use ($key) {
                return new Credentials(
                    $key,
                    'sha256',
                    'exqbZWtykFZIh2D7cXi9dA'
                );
            }
        );

        $server = ServerBuilder::create($credentialsProvider)
            ->setLocalTimeOffsetSec($localTimeOffsetSec)
            ->build();

        try {
            $result = $server->authenticateBewit($host, $port, $resource);

            if ($expected === true) {
                $this->assertTrue($result instanceof Response, $message);
                $this->assertEquals($key, $result->credentials()->key(), $message);
            } else {
                $this->fail($message);
            }
        } catch (\Exception $e) {
            if (is_string($expected)) {
                $this->assertEquals($expected, $e->getMessage(), $message);
            } else {
                $this->fail($message);
            }
        }
    }

    /**
     * @return array
     */
    public function bewitDataProvider()
    {
        $now = DefaultTimeProviderFactory::create()->createTimestamp();

        return [
            [
                'example.com',
                443,
                '/posts?bewit=ZXhxYlpXdHlrRlpJaDJEN2NYaTlkQVwxMzY4OTk2ODAwXE8wbWhwcmdvWHFGNDhEbHc1RldBV3ZWUUlwZ0dZc3FzWDc2dHBvNkt5cUk9XA',
                1368996800 - $now,
                true,
                'Should accept valid bewit request',
            ],
            [
                'example.com',
                443,
                '/posts',
                1368996800 - $now,
                'Malformed resource or does not contan bewit',
                'Should reject (missing bewit)',
            ],
            [
                'example.com',
                80,
                '/posts?bewit=ZXhxYlpXdHlrRlpJaDJEN2NYaTlkQVwxMzY4OTk2ODAwXE8wbWhwcmdvWHFGNDhEbHc1RldBV3ZWUUlwZ0dZc3FzWDc2dHBvNkt5cUk9XA',
                1368996800 - $now,
                'Bad MAC',
                'Should reject on invalid Bewit',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider headerDataProvider
     *
     * @param Credentials $credentials
     * @param Artifacts $artifacts
     * @param array $options
     * @param mixed $expectedHeader False if the header is expected to throw an exception
     * @param string $message
     * @return void
     */
    public function shouldTestHeader(Credentials $credentials, Artifacts $artifacts, array $options, $expectedHeader, $message)
    {
        $server = ServerBuilder::create(
            new CallbackCredentialsProvider(
                function ($id) {
                    // We don't need this for testing header
                    return false;
                }
            )
        )->build();

        if ($expectedHeader === false) {
            $this->setExpectedException('InvalidArgumentException');
        }

        $header = $server->createHeader($credentials, $artifacts, $options);

        $this->assertEquals($expectedHeader, $header->fieldValue(), $message);
    }

    /**
     * @return array
     */
    public function headerDataProvider()
    {
        return [
            [
                new Credentials('werxhqb98rpaxn39848xrunpaw3489ruxnpa98w4rxn', 'sha256', 123456),
                new Artifacts('POST', 'example.com', 8080, '/resource/4?filter=a', 1398546787, 'xUwusx', 'some-app-data'),
                ['payload' => 'some reply', 'content_type' => 'text/plain', 'ext' => 'response-specific'],
                'Hawk mac="n14wVJK4cOxAytPUMc5bPezQzuJGl5n7MYXhFQgEKsE=", hash="f9cDF/TDm7TkYRLnGwRMfeDzT6LixQVLvrIKhh0vgmM=", ext="response-specific"',
                'Should generate a valid header (with payload)',
            ],
            [
                new Credentials('werxhqb98rpaxn39848xrunpaw3489ruxnpa98w4rxn', 'sha256', 123456),
                new Artifacts('POST', 'example.com', 8080, '/resource/4?filter=a', 1398546787, 'xUwusx', 'some-app-data'),
                ['payload' => '', 'content_type' => 'text/plain', 'ext' => 'response-specific'],
                'Hawk mac="i8/kUBDx0QF+PpCtW860kkV/fa9dbwEoe/FpGUXowf0=", hash="q/t+NNAkQZNlq/aAD6PlexImwQTxwgT2MahfTa9XRLA=", ext="response-specific"',
                'Should generate a valid header (without payload)',
            ],
            [
                new Credentials('werxhqb98rpaxn39848xrunpaw3489ruxnpa98w4rxn', 'sha256', 123456),
                new Artifacts('POST', 'example.com', 8080, '/resource/4?filter=a', 1398546787, 'xUwusx', 'some-app-data'),
                ['payload' => 'some reply', 'content_type' => 'text/plain', 'ext' => null],
                'Hawk mac="6PrybJTJs20jsgBw5eilXpcytD8kUbaIKNYXL+6g0ns=", hash="f9cDF/TDm7TkYRLnGwRMfeDzT6LixQVLvrIKhh0vgmM="',
                'Should generate a valid header (without ext)',
            ],
            [
                new Credentials('werxhqb98rpaxn39848xrunpaw3489ruxnpa98w4rxn', 'sha256'),
                new Artifacts('POST', 'example.com', 8080, '/resource/4?filter=a', 1398546787, 'xUwusx', 'some-app-data'),
                ['payload' => 'some reply', 'content_type' => 'text/plain', 'ext' => null],
                'Hawk mac="6PrybJTJs20jsgBw5eilXpcytD8kUbaIKNYXL+6g0ns=", hash="f9cDF/TDm7TkYRLnGwRMfeDzT6LixQVLvrIKhh0vgmM="',
                'Should generate generate a header (missing credentials ID)',
            ],
            [
                new Credentials(null, 'sha256', 123456),
                new Artifacts('POST', 'example.com', 8080, '/resource/4?filter=a', 1398546787, 'xUwusx', 'some-app-data'),
                ['payload' => 'some reply', 'content_type' => 'text/plain', 'ext' => null],
                false,
                'Should throw an exception (missing credentials key)',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider authenticateDataProvider
     *
     * @param string $method
     * @param string $host
     * @param int $port
     * @param string $resource
     * @param string $contentType
     * @param mixed $payload
     * @param string $header
     * @param int $localTimeOffsetSec
     * @param mixed $expected true if a successful result is expected otherwise exception's message
     * @param string $message
     */
    public function shouldTestAuthentication($method, $host, $port, $resource, $contentType, $payload, $header,
                                             $localTimeOffsetSec, $expected, $message)
    {
        $key = 'werxhqb98rpaxn39848xrunpaw3489ruxnpa98w4rxn';

        $serverBuilder = ServerBuilder::create(
            new CallbackCredentialsProvider(
                function ($id) use ($key) {
                    return new Credentials(
                        $key,
                        $id == 1 ? 'sha1' : 'sha256',
                        $id
                    );
                }
            )
        );

        if (!empty($localTimeOffsetSec)) {
            $serverBuilder->setLocaltimeOffsetSec($localTimeOffsetSec);
        }

        $server = $serverBuilder->build();

        try {
            $result = $server->authenticate($method, $host, $port, $resource, $contentType, $payload, $header);

            if ($expected === true) {
                $this->assertTrue($result instanceof Response, $message);
                $this->assertEquals($key, $result->credentials()->key(), $message);
            } else {
                $this->fail($message);
            }
        } catch (\Exception $e) {
            if (is_string($expected)) {
                $this->assertEquals($expected, $e->getMessage(), $message);
            } else {
                $this->fail($message);
            }
        }
    }

    /**
     * @return array
     */
    public function authenticateDataProvider()
    {
        $timeProvider = DefaultTimeProviderFactory::create();
        $now = $timeProvider->createTimestamp();

        return [
            [
                'GET',
                'example.com',
                8080,
                '/resource/4?filter=a',
                null,
                null,
                'Hawk id="1", ts="1353788437", nonce="k3j4h2", mac="zy79QQ5/EYFmQqutVnYb73gAc/U=", ext="hello"',
                1353788437 - $now,
                true,
                'Should accept valid authentication request (GET with sha1)',
            ],
            [
                'GET',
                'example.com',
                8000,
                '/resource/1?b=1&a=2',
                null,
                null,
                'Hawk id="dh37fgj492je", ts="1353832234", nonce="j4h3g2", mac="m8r1rHbXN6NgO+KIIhjO7sFRyd78RNGVUwehe8Cp2dU=", ext="some-app-data"',
                1353832234 - $now,
                true,
                'Should accept valid authentication request (GET with sha256)',
            ],
            [
                'POST',
                'example.com',
                8080,
                '/resource/4?filter=a',
                null,
                null,
                'Hawk id="123456", ts="1357926341", nonce="1AwuJD", hash="qAiXIVv+yjDATneWxZP2YCTa9aHRgQdnH9b3Wc+o3dg=", ext="some-app-data", mac="UeYcj5UoTVaAWXNvJfLVia7kU3VabxCqrccXP8sUGC4="',
                1357926341 - $now,
                true,
                'Should accept valid authentication request (POST with sha256)',
            ],
            [
                'POST',
                'example.com',
                8080,
                '/resource/4?filter=a',
                null,
                'some message',
                'Hawk id="123456", ts="1357926341", nonce="1AwuJD", hash="FF897AJ2LPnv/0ilMuEgXBWGImE+/9TuSfw1oi4Rsqk=", ext="some-app-data", mac="aMy5l6U7ePOyX0Kk2Aq3wEJhmKyWtiQqYBEgfymhKns="',
                1357926341 - $now,
                true,
                'Should accept valid authentication request (POST with payload)',
            ],
            [
                'POST',
                'example.com',
                8080,
                '/resource/4?filter=a',
                null,
                'some message',
                'Hawk id="123456", ts="1357926341", nonce="1AwuJD", hash="FF897AJ2LPnv/0ilMuEgXBWGImE+/9TuSfw1oi4Rsqk=", ext="some-app-data", mac="aMy5l6U7ePOyX0KtiQqYBEgfymhKns="',
                1357926341 - $now,
                'Bad MAC',
                'Should reject for invalid Mac',
            ],
            [
                'POST',
                'example.com',
                8080,
                '/resource/4?filter=a',
                null,
                'some message',
                'Hawk id="123456", ts="1357926341", nonce="1AwuJD", hash="qAiXIVv+yjDATneWxZP2YCTa9aHRgQdnH9b3Wc+o3dg=", ext="some-app-data", mac="UeYcj5UoTVaAWXNvJfLVia7kU3VabxCqrccXP8sUGC4="',
                1357926341 - $now,
                'Bad payload hash',
                'Should reject for invalid hash',
            ],
            [
                'POST',
                'example.com',
                8080,
                '/resource/4?filter=a',
                null,
                'some message',
                'Hawk id="123456", ts="1357926341", nonce="1AwuJD", ext="some-app-data", mac="XCqOLBuIUZQoNZzTtikW0v06zJhhDNGiKWNfuErWLJ4="',
                1357926341 - $now,
                'Missing required payload hash',
                'Should reject for missing payload hash',
            ],
            [
                'POST',
                'example.com',
                8080,
                '/resource/4?filter=a',
                null,
                'some message',
                'HawkUseless header',
                1357926341 - $now,
                'Missing attributes',
                'Should reject for invalid header',
            ],
            [
                'POST',
                'example.com',
                8080,
                '/resource/4?filter=a',
                null,
                'some message',
                'Hawk ts="1353788437", nonce="k3j4h2", mac="/qwS4UjfVWMcUyW6EEgUH4jlr7T/wuKe3dKijvTvSos=", ext="hello"',
                1357926341 - $now,
                'Missing attributes',
                'Should reject for invalid header (missing ID)',
            ],
            [
                'POST',
                'example.com',
                8080,
                '/resource/4?filter=a',
                null,
                'some message',
                'Hawk id="123", ts="1353788437", mac="/qwS4UjfVWMcUyW6EEgUH4jlr7T/wuKe3dKijvTvSos=", ext="hello"',
                1357926341 - $now,
                'Missing attributes',
                'Should reject for invalid header (missing nonce)',
            ],
            [
                'POST',
                'example.com',
                8080,
                '/resource/4?filter=a',
                null,
                'some message',
                'Hawk id="  ", ts="1353788437", nonce="k3j4h2", mac="/qwS4UjfVWMcUyW6EEgUH4jlr7T/wuKe3dKijvTvSos=", ext="hello"',
                1357926341 - $now,
                'Missing attributes',
                'Should reject for invalid header (invalid ID)',
            ],
        ];
    }

    /**
     * @test
     *
     * Tests for replay nonce attack
     */
    public function shouldTestReplay()
    {
        $key = 'werxhqb98rpaxn39848xrunpaw3489ruxnpa98w4rxn';

        $serverBuilder = ServerBuilder::create(
            new CallbackCredentialsProvider(
                function ($id) use ($key) {
                    return new Credentials(
                        $key,
                        $id == 1 ? 'sha1' : 'sha256',
                        $id
                    );
                }
            )
        );

        $serverBuilder->setNonceValidator(
            new CallbackNonceValidator(
                function ($nonce, $timestamp) {
                    static $memory = array();
                    if (isset($memory[$nonce])) {
                        throw new UnauthorizedException('Invalid nonce');
                    }
                    $memory[$nonce] = $timestamp;
                    return true;
                }
            )
        );
        $serverBuilder->setLocaltimeOffsetSec(1353788437 - time());

        $server = $serverBuilder->build();

        try {
            for ($i = 0; $i < 2; $i++) {
                $server->authenticate(
                    'GET',
                    'example.com',
                    8080,
                    '/resource/4?filter=a',
                    null,
                    null,
                    'Hawk id="1", ts="1353788437", nonce="k3j4h2", mac="zy79QQ5/EYFmQqutVnYb73gAc/U=", ext="hello"'
                );
            }

            $this->fail('Should reject duplicate nonce');
        } catch (UnauthorizedException $e) {
            $this->assertEquals('Invalid nonce', $e->getMessage());
        }
    }

    /**
     * @test
     * @dataProvider messageDataProvider
     *
     * @param string $host
     * @param int $port
     * @param string $message
     * @param Message $authorization
     * @param int $localTimeOffsetSec
     * @param mixed $expected
     * @param string $testMessage
     */
    public function shouldTestMessage($host, $port, $message, Message $authorization, $localTimeOffsetSec, $expected, $testMessage)
    {
        $key = '2983d45yun89q';

        $serverBuilder = ServerBuilder::create(
            new CallbackCredentialsProvider(
                function ($id) use ($key) {
                    return new Credentials(
                        $key,
                        $id == 1 ? 'sha1' : 'sha256',
                        $id
                    );
                }
            )
        );

        if (!empty($localTimeOffsetSec)) {
            $serverBuilder->setLocaltimeOffsetSec($localTimeOffsetSec);
        }

        $server = $serverBuilder->build();

        try {
            $result = $server->authenticateMessage($host, $port, $message, $authorization);

            if ($expected === true) {
                $this->assertTrue($result instanceof Response, $testMessage);
                $this->assertEquals($key, $result->credentials()->key(), $testMessage);
            } else {
                $this->fail($testMessage);
            }
        } catch (\Exception $e) {
            if (is_string($expected)) {
                $this->assertEquals($expected, $e->getMessage(), $testMessage);
            } else {
                die(var_dump($e->getMessage()));
                $this->fail($testMessage);
            }
        }
    }

    /**
     * @return array
     */
    public function messageDataProvider()
    {
        $timeProvider = DefaultTimeProviderFactory::create();
        $now = $timeProvider->createTimestamp();

        return [
            [
                'example.net',
                80,
                'I am the boodyman',
                new Message(123456, 1353809207, 'abc123', '8bu1yuaHAgWqdTzyqwocrHNxVvGk9qXMVL7XC5FlsMo=', 'fWpeQac+YUDgpFkOXiJCfHXV19FHU6uKJh2pXyKa8BQ='),
                1353809207 - $now,
                true,
                'Should authenticate message',
            ],
            [
                'example.net',
                80,
                'I am the boodyman',
                new Message(123456, 1353809207, 'abc123', '8bu1yuaHAgWqdTzyqwocrHNxVvGk9qXMVL7XC5FlsMo=', 'fQac+YUDgpFkOXiJCfHXV19FHU6uKJh2pXyKa8BQ='),
                1353809207 - $now,
                'Bad MAC',
                'Should fail for invalid MAC',
            ],
            [
                'example.net',
                80,
                'I am the boodyman',
                new Message(123456, 1353809207, 'abc123', 'aaaabbb', '8pIsaWSf/s/0E0SNZnSzJ3bOKI9j5r0ehKPrdVZXJQs='),
                1353809207 - $now,
                'Bad payload hash',
                'Should fail for invalid payload hash',
            ],
        ];
    }
}
