<?php

use Boots\Container;
use Boots\Repository;
use org\bovigo\vfs\vfsStream;

class ContainerTest extends PHPUnit_Framework_TestCase
{
    protected $container;

    public function setUp()
    {
        vfsStream::setup('boots', null, [
            'container' => [
                'WithZeroParams.php' =>
                    '<?php namespace Boots\Test\Container;
                        class WithZeroParams {}',
                'WithOneParam.php' =>
                    '<?php namespace Boots\Test\Container;
                        class WithOneParam {
                            public function __construct(WithZeroParams $w0) {}
                        }',
                'WithTwoParams.php' =>
                    '<?php namespace Boots\Test\Container;
                        class WithTwoParams {
                            public function __construct(WithZeroParams $w0, WithOneParam $w1) {}
                        }',
                //
            ],
        ]);

        require_once vfsStream::url('boots/container/WithZeroParams.php');
        require_once vfsStream::url('boots/container/WithOneParam.php');
        require_once vfsStream::url('boots/container/WithTwoParams.php');

        $this->container = new Container(new Repository);
    }

    /** @test */
    public function it_should_be_an_implementation_of_a_contract()
    {
        $this->assertInstanceOf('Boots\Contract\ContainerContract', $this->container);
    }

    /** @test */
    public function it_should_add_and_retrieve_an_entity()
    {
        $this->container->add('foo', 'bar');
        $this->assertEquals('bar', $this->container->get('foo'));
    }

    /** @test */
    public function it_should_resolve_an_unbinded_object_if_class_constructor_is_not_defined_or_has_no_params()
    {
        $class = 'Boots\Test\Container\WithZeroParams';
        $this->assertInstanceOf($class, $this->container->get($class));
    }

    /** @test */
    public function it_should_resolve_an_object_if_class_constructor_params_can_be_resolved_recursively()
    {
        $class = 'Boots\Test\Container\WithTwoParams';
        $this->assertInstanceOf($class, $this->container->get($class));
    }
}
