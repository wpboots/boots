<?php

use Boots\Boots;

class Api_2_0_0_Test extends PHPUnit_Framework_TestCase
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
    public function it_should_throw_exception_if_type_is_invalid()
    {
        $this->setExpectedException('Boots\Exception\InvalidTypeException');
        new Boots(null, ['abspath' => __FILE__]);
    }

    /** @test */
    public function it_should_throw_exception_if_id_not_provided()
    {
        $this->setExpectedException('Boots\Exception\InvalidConfigException');
        new Boots('plugin', ['abspath' => __FILE__]);
    }

    /** @test */
    public function it_should_throw_exception_if_nick_not_provided()
    {
        $this->setExpectedException('Boots\Exception\InvalidConfigException');
        new Boots('plugin', [
            'abspath' => __FILE__,
            'id' => 'boots_test',
        ]);
    }

    /** @test */
    public function it_should_throw_exception_if_version_not_provided()
    {
        $this->setExpectedException('Boots\Exception\InvalidConfigException');
        new Boots('plugin', [
            'abspath' => __FILE__,
            'id' => 'boots_test',
            'nick' => 'Boots Test',
        ]);
    }

    /** @test */
    public function it_should_set_the_default_env()
    {
        $this->assertEquals('production', $this->boots->getConfig()->get('env'));
        $boots = new Boots('plugin', [
            'abspath' => __FILE__,
            'id' => 'boots_test',
            'nick' => 'Boots Test',
            'version' => '0.1.0',
            'env' => 'local',
        ]);
        $this->assertEquals('local', $boots->getConfig()->get('env'));
    }

    /** @test */
    public function it_should_set_the_type_of_application()
    {
        $this->assertEquals('plugin', $this->boots->getConfig()->get('app.type'));
    }

    /** @test */
    public function it_should_set_the_app_main_file()
    {
        $this->assertEquals(basename(__FILE__), $this->boots->getConfig()->get('app.file'));
    }

    /** @test */
    public function it_should_set_the_app_path()
    {
        $this->assertEquals(dirname(__FILE__), $this->boots->getConfig()->get('app.path'));
    }
}