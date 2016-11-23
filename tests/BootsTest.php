<?php

use Boots\Boots;

class BootsTest extends PHPUnit_Framework_TestCase
{
    protected $boots;

    public function setUp()
    {
        $this->boots = new Boots('plugin', [
            'abspath' => __FILE__,
            'id' => 'boots_test',
            'nick' => 'Boots Test',
            'version' => '0.1.0',
        ]);
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
        $config = $this->boots->getConfig();
        $this->assertEquals(__FILE__, $config->get('abspath'));
        $this->assertEquals('boots_test', $config->get('id'));
        $this->assertEquals('Boots Test', $config->get('nick'));
        $this->assertEquals('0.1.0', $config->get('version'));
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