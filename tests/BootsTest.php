<?php

use Boots\Boots;

class BootsTest extends PHPUnit_Framework_TestCase
{
    protected $boots;

    protected $abspath;

    protected $appPath;

    public function setUp()
    {
        $this->appPath = dirname(dirname(dirname(__FILE__)));
        $this->abspath = "{$this->appPath}/index.php";
        $this->boots = new Boots('plugin', [
            'abspath' => $this->abspath,
            'id' => 'boots_test',
            'nick' => 'Boots Test',
            'version' => '0.1.0',
        ]);
    }

    /** @test */
    public function it_should_setup_the_manifest_repository()
    {
        $expectedArray = [
            'version' => '',
        ];
        $this->assertEquals($expectedArray, $this->boots->getManifest()->all());
    }

    /** @test */
    public function it_should_setup_the_configuration_repository()
    {
        $config = $this->boots->getConfig();
        $this->assertEquals($this->abspath, $config->get('abspath'));
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
        $this->assertEquals('', $this->boots->getVersion());
    }

    /** @test */
    public function it_should_return_the_api_instance()
    {
        $this->assertInstanceOf('Boots\Api', $this->boots->getInstance());
    }
}