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
}