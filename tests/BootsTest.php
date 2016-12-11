<?php

use Boots\Boots;
use Boots\Exception\NotFoundException;

class BootsTest extends PHPUnit_Framework_TestCase
{
    protected $version = 'x.x.x';

    protected $config;

    protected $dispenser;

    protected $boots;

    public function setUp()
    {
        $this->dispenser = \Mockery::mock('Boots\Contract\DispenserContract');
        $this->config = \Mockery::mock('Boots\Contract\RepositoryContract');
        $this->boots = new Boots($this->version, $this->dispenser, $this->config);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /** @test */
    public function version_method_should_return_the_framework_version()
    {
        $this->assertEquals($this->version, $this->boots->version());
    }

    /** @test */
    public function config_method_with_one_arg_should_return_the_value_for_that_key()
    {
        $this->config->shouldReceive('get')->with('foo')->once()->andReturn('bar');
        $this->assertEquals('bar', $this->boots->config('foo'));
    }

    /** @test */
    public function config_method_with_two_args_should_set_and_return_a_value_for_that_key()
    {
        $this->config->shouldReceive('set')->with('bar', 'baz')->once();
        $this->assertEquals('baz', $this->boots->config('bar', 'baz'));
    }

    /** @test */
    public function config_method_with_zero_args_should_return_the_repository_instance()
    {
        $this->assertInstanceOf('Boots\Contract\RepositoryContract', $this->boots->config());
    }

    /** @test */
    public function __get_magic_method_should_return_an_extension()
    {
        $obj = new stdClass;
        $obj->foo = 'bar';
        $this->dispenser->shouldReceive('dispense')->with('acme')->once()->andReturn($obj);
        $acme = $this->boots->acme;
        $this->assertInstanceOf('stdClass', $acme);
        $this->assertNotNull($acme->foo);
        $this->assertEquals('bar', $acme->foo);
    }

    /** @test */
    public function __get_magic_method_should_throw_NotFoundException_if_extension_not_found()
    {
        $this->setExpectedException('Boots\Exception\NotFoundException');
        $this->dispenser->shouldReceive('dispense')->with('nah')->once()
            ->andThrow(new NotFoundException);
        $this->boots->nah;
    }
}