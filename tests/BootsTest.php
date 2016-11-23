<?php

use Boots\Boots;

class BootsTest extends PHPUnit_Framework_TestCase
{
    protected $boots;

    protected $config;

    public function setUp()
    {
        $this->config = [
            'abspath' => __FILE__,
            'id' => 'boots_test',
            'nick' => 'Boots Test',
            'version' => '0.1.0',
        ];

        $this->boots = new Boots('plugin', $this->config);
    }

    /** @test */
    public function it_should_setup_the_manifest_repository()
    {
        $expectedArray = [
            'version' => '2.0.0',
            'repository' => [
                'version' => '2.0.0',
            ],
        ];
        $this->assertEquals($expectedArray, $this->boots->getManifest()->all());
    }

    /** @test */
    public function it_should_setup_the_configuration_repository()
    {
        $this->assertEquals($this->config, $this->boots->getConfig()->all());
    }

    /** @test */
    public function it_should_return_the_type_of_the_application()
    {
        $this->assertEquals('plugin', $this->boots->getType());
    }

    /** @test */
    public function it_should_return_the_version_of_the_api()
    {
        $this->assertEquals('2.0.0', $this->boots->getVersion());
    }

    /** @test */
    public function it_should_return_the_api_instance()
    {
        $this->assertInstanceOf('Boots\Api_2_0_0', $this->boots->getInstance());
    }
}