<?php

use Boots\Boots;
use org\bovigo\vfs\vfsStream;
use Boots\Exception\NotFoundException;

class BootsTest extends PHPUnit_Framework_TestCase
{
    protected $config;

    protected $dispenser;

    protected $boots;

    protected $factory;

    public function setUp()
    {
        $this->dispenser = \Mockery::mock('Boots\Contract\DispenserContract');
        $this->config = \Mockery::mock('Boots\Contract\RepositoryContract');
        $this->boots = new Boots($this->dispenser, $this->config);

        vfsStream::setup('boots', null, [
            'boots-app' => [
                'boots' => [
                    'extend' => [
                        'acme' => [
                            'Dep.php' => '<?php namespace Boots\Test\Boots; class Dep_x_y_z {}',
                            'Acme.php' => '<?php namespace Boots\Test\Boots;
                                use Boots\Boots;
                                class Acme_x_y_z {
                                    public $dep;
                                    public $boots;
                                    public function __construct(Dep_x_y_z $dep, Boots $boots)
                                    {
                                        $this->dep = $dep;
                                        $this->boots = $boots;
                                    }
                                }
                            ',
                        ],
                    ],
                    'boots.php' => '<?php return [
                        "version" => "1.2.3",
                        "extensions" => [
                            "acme" => [
                                "version" => "x.y.z",
                                "class" => "Boots\Test\Boots\Acme",
                                "autoload" => [
                                    "psr-4" => [
                                        "Boots\\\Test\\\Boots\\\" => "",
                                    ]
                                ],
                            ],
                        ],
                    ];',
                ],
            ],
        ]);

        $this->factory = Boots::create(
            vfsStream::url('boots/boots-app'),
            ['foo' => ['bar' => 'baz']]
        );
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /** @test */
    public function create_static_method_should_return_an_instance_of_boots()
    {
        $this->assertInstanceOf('Boots\Boots', $this->factory);
    }

    /** @test */
    public function config_method_with_one_arg_should_return_the_value_for_that_key()
    {
        $this->config->shouldReceive('get')->with('foo')->once()->andReturn('bar');
        $this->assertEquals('bar', $this->boots->config('foo'));

        $this->assertEquals('baz', $this->factory->config('foo.bar'));
    }

    /** @test */
    public function config_method_with_two_args_should_set_and_return_a_value_for_that_key()
    {
        $this->config->shouldReceive('set')->with('bar', 'baz')->once();
        $this->config->shouldReceive('get')->with('bar')->once()->andReturn('baz');
        $this->assertEquals('baz', $this->boots->config('bar', 'baz'));
        $this->assertEquals('baz', $this->boots->config('bar'));

        $this->assertEquals('beep', $this->factory->config('foo.baz', 'beep'));
        $this->assertEquals('beep', $this->factory->config('foo.baz'));
    }

    /** @test */
    public function config_method_with_zero_args_should_return_the_repository_instance()
    {
        $this->assertInstanceOf('Boots\Contract\RepositoryContract', $this->boots->config());

        $this->assertInstanceOf('Boots\Contract\RepositoryContract', $this->factory->config());
    }

    /** @test */
    public function version_should_be_set_when_constructed_via_factory()
    {
        $this->assertEquals('1.2.3', $this->factory->config('boots.version'));
    }

    /** @test */
    public function path_should_be_set_when_constructed_via_factory()
    {
        $this->assertEquals(
            vfsStream::url('boots/boots-app/boots'),
            $this->factory->config('boots.path')
        );
    }

    /** @test */
    public function extend_path_should_be_set_when_constructed_via_factory()
    {
        $this->assertEquals(
            vfsStream::url('boots/boots-app/boots/extend'),
            $this->factory->config('boots.extend_path')
        );
    }

    /** @test */
    public function app_path_should_be_set_when_constructed_via_factory()
    {
        $this->assertEquals(
            vfsStream::url('boots/boots-app'),
            $this->factory->config('app.path')
        );
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

        $acme = $this->factory->acme;
        $this->assertInstanceOf('Boots\Test\Boots\Acme_x_y_z', $acme);
        $this->assertInstanceOf('Boots\Test\Boots\Dep_x_y_z', $acme->dep);
        $this->assertInstanceOf('Boots\Boots', $acme->boots);
        $this->assertSame($this->factory, $acme->boots);
    }

    /** @test */
    public function __get_magic_method_should_throw_NotFoundException_if_extension_not_found()
    {
        $this->setExpectedException('Boots\Exception\NotFoundException');
        $this->dispenser->shouldReceive('dispense')->with('nah')->once()
            ->andThrow(new NotFoundException);
        $this->boots->nah;
    }

    /** @test */
    public function on_factory__get_magic_method_should_throw_NotFoundException_if_extension_not_found()
    {
        $this->setExpectedException('Boots\Exception\NotFoundException');
        $this->factory->nah;
    }
}