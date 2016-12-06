<?php

use Boots\Container;
use org\bovigo\vfs\vfsStream;

class ContainerTest extends PHPUnit_Framework_TestCase
{
    protected $container;

    public function setUp()
    {
        vfsStream::setup('boots', null, [
            'container' => [
                'Foobar.php' =>
                    '<?php namespace Boots\Test\Container;
                        class Foobar {
                            public function __construct($foo) {}
                        }',
                'WithoutConstructor.php' =>
                    '<?php namespace Boots\Test\Container;
                        class WithoutConstructor {}',
                'WithZeroParams.php' =>
                    '<?php namespace Boots\Test\Container;
                        class WithZeroParams {
                            public function __construct() {}
                        }',
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
                'WithManagedParams.php' =>
                    '<?php namespace Boots\Test\Container;
                        class WithManagedParams {
                            public function __construct(Foobar $foo, WithOneParam $w1) {}
                        }',
                'WithOptionalParams.php' =>
                    '<?php namespace Boots\Test\Container;
                        class WithOptionalParams {
                            public $a;
                            public function __construct(WithTwoParams $w2, $a = "b") {
                                $this->a = $a;
                            }
                        }',
                'Invocable.php' =>
                    '<?php namespace Boots\Test\Container;
                        class Invocable {
                            public function __invoke() { return "invoke"; }
                        }',
                'InvocableWithParams.php' =>
                    '<?php namespace Boots\Test\Container;
                        class InvocableWithParams {
                            public function __invoke(WithZeroParams $w0, WithOneParam $w1) {
                                return "invoke + params";
                            }
                        }',
                'Contract.php' =>
                    '<?php namespace Boots\Test\Container;
                        interface Contract {
                            public function contract();
                        }',
                'Concrete.php' =>
                    '<?php namespace Boots\Test\Container;
                        class Concrete implements Contract {
                            public function __construct(WithZeroParams $w0, WithOneParam $w1) {}
                            public function contract() {}
                        }',
                'ContractParam.php' =>
                    '<?php namespace Boots\Test\Container;
                        class ContractParam {
                            public $concrete;
                            public function __construct(Contract $concrete) {
                                $this->concrete = $concrete;
                            }
                        }',
                //
            ],
        ]);

        require_once vfsStream::url('boots/container/Foobar.php');
        require_once vfsStream::url('boots/container/WithoutConstructor.php');
        require_once vfsStream::url('boots/container/WithZeroParams.php');
        require_once vfsStream::url('boots/container/WithOneParam.php');
        require_once vfsStream::url('boots/container/WithTwoParams.php');
        require_once vfsStream::url('boots/container/WithManagedParams.php');
        require_once vfsStream::url('boots/container/WithOptionalParams.php');
        require_once vfsStream::url('boots/container/Invocable.php');
        require_once vfsStream::url('boots/container/InvocableWithParams.php');
        require_once vfsStream::url('boots/container/Contract.php');
        require_once vfsStream::url('boots/container/Concrete.php');
        require_once vfsStream::url('boots/container/ContractParam.php');

        $this->container = new Container;
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
        $class = 'Boots\Test\Container\WithoutConstructor';
        $this->assertInstanceOf($class, $this->container->get($class));

        $class = 'Boots\Test\Container\WithZeroParams';
        $this->assertInstanceOf($class, $this->container->get($class));
    }

    /** @test */
    public function it_should_resolve_an_object_if_class_constructor_params_can_be_resolved_recursively()
    {
        $class = 'Boots\Test\Container\WithTwoParams';
        $this->assertInstanceOf($class, $this->container->get($class));
    }

    /** @test */
    public function it_should_resolve_an_object_if_class_constructor_params_are_being_managed()
    {
        $foobarClass = 'Boots\Test\Container\Foobar';

        $this->container->add('foo', 'bar');
        $this->assertInstanceOf($foobarClass, $this->container->get($foobarClass));

        $class = 'Boots\Test\Container\WithManagedParams';
        $this->assertInstanceOf($class, $this->container->get($class));
    }

    /** @test */
    public function it_should_resolve_a_class_if_constructor_has_optional_params()
    {
        $class = 'Boots\Test\Container\WithOptionalParams';
        $this->assertInstanceOf($class, $this->container->get($class));

        $this->container->add('a', 'c');
        $WithOptionalParams = $this->container->get($class);
        $this->assertInstanceOf($class, $WithOptionalParams);
        $this->assertEquals('c', $WithOptionalParams->a);
    }

    /** @test */
    public function it_should_resolve_a_callable()
    {
        $this->container->add('a', function () {
            return 'b';
        });
        $this->assertEquals('b', $this->container->get('a'));

        $foobarClass = 'Boots\Test\Container\Foobar';
        $this->container->add($foobarClass, function () use ($foobarClass) {
            return new $foobarClass('baz');
        });
        $this->assertInstanceOf($foobarClass, $this->container->get($foobarClass));
    }

    /** @test */
    public function it_should_resolve_a_callable_with_params()
    {
        $this->container->add('callable', function (Boots\Test\Container\WithTwoParams $w2) {
            return 'it works!';
        });
        $this->assertEquals('it works!', $this->container->get('callable'));
    }

    /** @test */
    public function it_should_resolve_an_invocable()
    {
        $this->container->add('invocable', new Boots\Test\Container\Invocable);
        $this->assertEquals('invoke', $this->container->get('invocable'));
    }

    /** @test */
    public function it_should_resolve_an_invocable_with_params()
    {
        $this->container->add('invocableWithParams', new Boots\Test\Container\InvocableWithParams);
        $this->assertEquals('invoke + params', $this->container->get('invocableWithParams'));
    }

    /** @test */
    public function it_should_resolve_an_interface()
    {
        $contractInterface = 'Boots\Test\Container\Contract';
        $concreteClass = 'Boots\Test\Container\Concrete';
        $concrete = $this->container->get($concreteClass);
        $this->assertInstanceOf($contractInterface, $concrete);
        $this->container->add($contractInterface, $concrete);
        $contractParamClass = 'Boots\Test\Container\ContractParam';
        $contractParam = $this->container->get($contractParamClass);
        $this->assertInstanceOf($contractParamClass, $contractParam);
    }

    /** @test */
    public function it_should_allow_singletons()
    {
        $w0Class = 'Boots\Test\Container\WithZeroParams';

        $this->container->share('shared', function () use ($w0Class) {
            return new $w0Class;
        });
        $resolvedShared1 = $this->container->get('shared');
        $this->assertInstanceOf($w0Class, $resolvedShared1);
        $resolvedShared2 = $this->container->get('shared');
        $this->assertInstanceOf($w0Class, $resolvedShared2);
        $this->assertSame($resolvedShared1, $resolvedShared2);

        $this->container->add('shared', function () use ($w0Class) {
            return new $w0Class;
        });
        $resolvedShared1 = $this->container->get('shared');
        $this->assertInstanceOf($w0Class, $resolvedShared1);
        $resolvedShared2 = $this->container->get('shared');
        $this->assertInstanceOf($w0Class, $resolvedShared2);
        $this->assertNotSame($resolvedShared1, $resolvedShared2);
    }

    /** @test */
    public function it_should_allow_singleton_interfaces()
    {
        $contractInterface = 'Boots\Test\Container\Contract';
        $concreteClass = 'Boots\Test\Container\Concrete';
        $this->container->share($contractInterface, function () use ($concreteClass) {
            return $this->container->get($concreteClass);
        });
        $contractParamClass = 'Boots\Test\Container\ContractParam';
        $contractParam1 = $this->container->get($contractParamClass);
        $this->assertInstanceOf($contractParamClass, $contractParam1);
        $contractParam2 = $this->container->get($contractParamClass);
        $this->assertInstanceOf($contractParamClass, $contractParam2);
        $this->assertSame($contractParam1->concrete, $contractParam2->concrete);

        $this->container->add($contractInterface, function () use ($concreteClass) {
            return $this->container->get($concreteClass);
        });
        $contractParamClass = 'Boots\Test\Container\ContractParam';
        $contractParam1 = $this->container->get($contractParamClass);
        $this->assertInstanceOf($contractParamClass, $contractParam1);
        $contractParam2 = $this->container->get($contractParamClass);
        $this->assertInstanceOf($contractParamClass, $contractParam2);
        $this->assertNotSame($contractParam1->concrete, $contractParam2->concrete);
    }

    // delegates

    // exceptions
}
