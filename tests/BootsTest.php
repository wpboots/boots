<?php

use Boots\Boots;
use Boots\Dispenser;
use org\bovigo\vfs\vfsStream;
use Boots\Exception\NotFoundException;

class BootsTest extends PHPUnit_Framework_TestCase
{
    protected $boots;

    public function setUp()
    {
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

        $this->boots = Boots::create(vfsStream::url('boots/boots-app'), [
            'foo' => ['bar' => 'baz'],
            'type' => 'plugin',
            'id' => 'foo',
            'nick' => 'Foo',
            'version' => 'a.b.c',
        ]);
    }

    /** @test */
    public function instance_of_boots_should_bind_itself_onto_the_container()
    {
        $boots = new Boots(new Dispenser(''));

        $entity = $boots->get('Boots\Boots');
        $this->assertInstanceOf('Boots\Boots', $entity);
        $this->assertSame($boots, $entity);

        $entity = $boots->get('boots');
        $this->assertInstanceOf('Boots\Boots', $entity);
        $this->assertSame($boots, $entity);
    }

    /** @test */
    public function create_static_method_should_return_an_instance_of_boots()
    {
        $this->assertInstanceOf('Boots\Boots', $this->boots);
    }

    /** @test */
    public function fire_and_on_should_be_proxied_to_the_event_dispatcher()
    {
        $this->boots->on('foo', function ($event, $bar) {
            return $bar;
        });
        $this->assertEquals(['bar'], $this->boots->fire('foo', ['bar']));
    }

    /** @test */
    public function config_method_with_one_arg_should_return_the_value_for_that_key()
    {
        $this->assertEquals('baz', $this->boots->config('foo.bar'));
    }

    /** @test */
    public function config_method_with_two_args_should_set_and_return_a_value_for_that_key()
    {
        $this->assertEquals('beep', $this->boots->config('foo.baz', 'beep'));
        $this->assertEquals('beep', $this->boots->config('foo.baz'));
    }

    /** @test */
    public function config_method_with_zero_args_should_return_the_repository_instance()
    {
        $this->assertInstanceOf('Boots\Contract\RepositoryContract', $this->boots->config());
    }

    /** @test */
    public function version_should_be_set_when_constructed_via_factory()
    {
        $this->assertEquals('1.2.3', $this->boots->config('boots.version'));
    }

    /** @test */
    public function path_should_be_set_when_constructed_via_factory()
    {
        $this->assertEquals(
            vfsStream::url('boots/boots-app/boots'),
            $this->boots->config('boots.path')
        );
    }

    /** @test */
    public function extend_path_should_be_set_when_constructed_via_factory()
    {
        $this->assertEquals(
            vfsStream::url('boots/boots-app/boots/extend'),
            $this->boots->config('boots.extend_path')
        );
    }

    /** @test */
    public function app_path_should_be_set_when_constructed_via_factory()
    {
        $this->assertEquals(
            vfsStream::url('boots/boots-app'),
            $this->boots->config('app.path')
        );
    }

    /** @test */
    public function extensions_should_be_set_when_constructed_via_factory()
    {
        $acme = $this->boots->config('extensions.acme');
        $this->assertEquals('x.y.z', $acme['version']);
        $this->assertEquals(vfsStream::url('boots/boots-app/boots/extend/acme'), $acme['path']);
    }

    /** @test */
    public function __get_magic_method_should_return_an_extension()
    {
        $acme = $this->boots->acme;
        $this->assertInstanceOf('Boots\Test\Boots\Acme_x_y_z', $acme);
        $this->assertInstanceOf('Boots\Test\Boots\Dep_x_y_z', $acme->dep);
        $this->assertInstanceOf('Boots\Boots', $acme->boots);
        $this->assertSame($this->boots, $acme->boots);
    }

    /** @test */
    public function __get_magic_method_should_throw_NotFoundException_if_extension_not_found()
    {
        $this->setExpectedException('Boots\Exception\NotFoundException');
        $this->boots->nah;
    }
}