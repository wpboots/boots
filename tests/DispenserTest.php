<?php

use Boots\Container;
use Boots\Dispenser;
use org\bovigo\vfs\vfsStream;

class DispenserTest extends PHPUnit_Framework_TestCase
{
    protected $container;

    protected $dispenser;

    public function setUp()
    {
        vfsStream::setup('boots', null, [
            'dispenser' => [
                'chocolate' => [
                    'Chocolate.php' => '<?php
                        namespace Boots\Test\Dispenser\Chocolate;
                        class Chocolate {
                            public function __construct()
                            {
                                $this->chocobar = new Chocobar;
                            }
                        }',
                    'src' => [
                        'Chocobar.php' => '<?php
                            namespace Boots\Test\Dispenser\Chocolate;
                            class Chocobar {}',
                    ],
                ],
                'apple-juice' => [
                    'src' => [
                        'Apple.php' => '<?php
                            namespace Boots\Test\Dispenser\Apple;
                            class Apple_1_2 {}',
                    ]
                ],
                'ioc' => [
                    'Ioc.php' => '<?php
                        namespace Boots\Test\Dispenser\Ioc;
                        class Binding {}
                        class Ioc {
                            public $binding;
                            public $ioc;
                            public function __construct(Binding $binding, $ioc)
                            {
                                $this->ioc = $ioc;
                                $this->binding = $binding;
                            }
                        }',
                    //
                ],
            ],
        ]);

        $manifest = [
            'chocolate' => [
                'version' => '',
                'class' => 'Boots\\Test\\Dispenser\\Chocolate\\Chocolate',
                'autoload' => [
                    'psr-4' => [
                        'Boots\\Test\\Dispenser\\Chocolate\\' => ['', 'src/'],
                    ],
                ],
            ],
            'apple-juice' => [
                'version' => '1.2',
                'class' => 'Boots\\Test\\Dispenser\\Apple\\Apple',
                'autoload' => [
                    'psr-4' => [
                        'Boots\\Test\\Dispenser\\Apple\\' => 'src/',
                    ],
                ],
            ],
            'ioc' => [
                'version' => '',
                'class' => 'Boots\\Test\\Dispenser\\Ioc\\Ioc',
                'autoload' => [
                    'psr-4' => [
                        'Boots\\Test\\Dispenser\\Ioc\\' => '',
                    ],
                ],
            ],
            'no-main-class' => [],
            'main-class-na' => ['class' => 'Boots\\Test\Dispenser\\MainClassNa'],
        ];

        $this->container = new Container;
        $directory = vfsStream::url('boots/dispenser');
        $this->dispenser = new Dispenser($directory, $manifest);
    }

    /** @test */
    public function it_should_be_an_implementation_of_a_contract()
    {
        $this->assertInstanceOf(
            'Boots\Contract\DispenserContract',
            $this->dispenser
        );
    }

    /** @test */
    public function it_should_dispense_an_entity_that_may_be_psr4_autoloaded()
    {
        $this->assertInstanceOf(
            'Boots\Test\Dispenser\Chocolate\Chocolate',
            $this->dispenser->dispense('chocolate')
        );
    }

    /** @test */
    public function it_should_dispense_a_versioned_entity_that_may_be_psr4_autoloaded()
    {
        $this->assertInstanceOf(
            'Boots\Test\Dispenser\Apple\Apple_1_2',
            $this->dispenser->dispense('appleJuice')
        );
    }

    /** @test */
    public function it_should_dispense_a_kebab_snake_or_camel_cased_entity_that_may_be_psr4_autoloaded()
    {
        $this->assertInstanceOf(
            'Boots\Test\Dispenser\Apple\Apple_1_2',
            $this->dispenser->dispense('apple_juice')
        );
        $this->assertInstanceOf(
            'Boots\Test\Dispenser\Apple\Apple_1_2',
            $this->dispenser->dispense('apple-juice')
        );
        $this->assertInstanceOf(
            'Boots\Test\Dispenser\Apple\Apple_1_2',
            $this->dispenser->dispense('appleJuice')
        );
    }

    /** @test */
    public function it_should_dispense_an_ioc_based_entity_that_may_be_psr4_autoloaded()
    {
        $this->container->add('ioc', 'foo');
        $this->dispenser->setContainer($this->container);
        $ioc = $this->dispenser->dispense('ioc');
        $this->assertInstanceOf('Boots\Test\Dispenser\Ioc\Ioc', $ioc);
        $this->assertInstanceOf('Boots\Test\Dispenser\Ioc\Binding', $ioc->binding);
        $this->assertEquals('foo', $ioc->ioc);
    }

    /** @test */
    public function it_should_throw_NotFoundException_if_extension_key_is_not_found_in_manifest()
    {
        $this->setExpectedException('Boots\Exception\NotFoundException');
        $this->dispenser->dispense('na');
    }

    /** @test */
    public function it_should_throw_InvalidExtensionException_if_extension_class_is_not_set_in_manifest()
    {
        $this->setExpectedException('Boots\Exception\InvalidExtensionException');
        $this->dispenser->dispense('no-main-class');
    }

    /** @test */
    public function it_should_throw_ClassNotFoundException_if_extension_class_is_not_found()
    {
        $this->setExpectedException('Boots\Exception\ClassNotFoundException');
        $this->dispenser->dispense('main-class-na');
    }
}