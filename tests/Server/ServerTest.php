<?php

namespace Dragooon\Hawk\Server;

use Dragooon\Hawk\Credentials\Credentials;
use Dragooon\Hawk\Nonce\NonceProviderInterface;
use Dragooon\Hawk\Time\ConstantTimeProvider;
use Dragooon\Hawk\Time\TimeProviderInterface;
use Dragooon\Hawk\Header\Header;
use Dragooon\Hawk\Crypto\Artifacts;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldAuthenticateBewit()
    {
        $credentialsProvider = function ($id) {
            return new Credentials(
                'HX9QcbD-r3ItFEnRcAuOSg',
                'sha256',
                'exqbZWtykFZIh2D7cXi9dA'
            );
        };

        $server = ServerBuilder::create($credentialsProvider)
            ->setTimeProvider(new ConstantTimeProvider(1368996800))
            ->build();

        $response = $server->authenticateBewit(
            'example.com',
            443,
            '/posts?bewit=ZXhxYlpXdHlrRlpJaDJEN2NYaTlkQVwxMzY4OTk2ODAwXE8wbWhwcmdvWHFGNDhEbHc1RldBV3ZWUUlwZ0dZc3FzWDc2dHBvNkt5cUk9XA'
        );

        $this->assertEquals('/posts', $response->artifacts()->resource());
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
            function($id) {
                // We don't need this for testing header
                return false;
            }
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
}
