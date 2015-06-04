<?php

namespace Dragooon\Hawk\Credentials;

use Dragooon\Hawk\Credentials\Credentials;
use Dragooon\Hawk\Nonce\NonceProviderInterface;
use Dragooon\Hawk\Time\TimeProviderInterface;

class CredentialsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCheckCredentials()
    {
        $credentials = new Credentials(
            'HX9QcbD-r3ItFEnRcAuOSg',
            'sha256',
            'exqbZWtykFZIh2D7cXi9dA'
        );

        $this->assertEquals('HX9QcbD-r3ItFEnRcAuOSg', $credentials->key());
        $this->assertEquals('sha256', $credentials->algorithm());
        $this->assertEquals('exqbZWtykFZIh2D7cXi9dA', $credentials->id());

        try {
            new Credentials(
                'HX9QcbD-r3ItFEnRcAuOSg',
                'example',
                'exqbZWtykFZIh2D7cXi9dA'
            );
        }
        catch (\InvalidArgumentException $e) {
            return true;
        }

        $this->fail('Credentials should throw an exception for having invalid algorithm');
    }
}