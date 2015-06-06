<?php

namespace Dragooon\Hawk\Header;

class HeaderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldTestObjectOrString()
    {
        $header = 'Hawk id="123456", ts="1353809207", nonce="Ygvqdz", hash="bsvY3IfUllw6V5rvk4tStEvpBhE=", ext="Bazinga!", mac="qbf1ZPG/r/e06F4ht+T77LXi5vw="';
        $header = HeaderFactory::createFromHeaderObjectOrString(
            'Test',
            $header,
            function() {
                return true;
            }
        );

        $this->assertTrue($header instanceof Header);
        $this->assertEquals(123456, $header->attribute('id'));
        $this->assertEquals('Ygvqdz', $header->attribute('nonce'));

        try {
            $header = 'InvalidHawkHeader';
            HeaderFactory::createFromHeaderObjectOrString(
                'Test',
                $header,
                function () {
                }
            );
            $this->fail('Should throw an exception for invalid header');
        } catch (NotHawkAuthorizationException $e) {
        }

        $header = HeaderFactory::createFromHeaderObjectOrString(
            'Test',
            new Header('Test', 'Hawk id="123456"', ['id' => 123456]),
            function() {
            }
        );
        $this->assertTrue($header instanceof Header);
        $this->assertEquals(123456, $header->attribute('id'));
    }


    /**
     * @test
     */
    public function shouldTestString()
    {
        $header = 'Hawk id="123456", ts="1353809207", nonce="Ygvqdz", hash="bsvY3IfUllw6V5rvk4tStEvpBhE=", ext="Bazinga!", mac="qbf1ZPG/r/e06F4ht+T77LXi5vw="';
        $header = HeaderFactory::createFromString('Test', $header);

        $this->assertTrue($header instanceof Header);
        $this->assertEquals(123456, $header->attribute('id'));
        $this->assertEquals('Ygvqdz', $header->attribute('nonce'));
    }
}