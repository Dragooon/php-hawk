<?php

namespace Dragooon\Hawk\Server;

use Dragooon\Hawk\Credentials\Credentials;
use Dragooon\Hawk\Nonce\NonceProviderInterface;
use Dragooon\Hawk\Time\ConstantTimeProvider;
use Dragooon\Hawk\Time\TimeProviderInterface;

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
}
