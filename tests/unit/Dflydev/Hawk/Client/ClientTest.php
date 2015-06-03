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
}
