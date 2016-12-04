<?php

use Boots\Locator;
use Boots\Dispenser;
use Boots\Repository;
use org\bovigo\vfs\vfsStream;

class DispenserTest extends PHPUnit_Framework_TestCase
{
    protected $dispenser;

    public function setUp()
    {
        vfsStream::setup('boots', null, [
            'dispenser' => [
                'chocolate' => [
                    'chocolate.php' => '<?php namespace Boots\Test\Dispenser; class Chocolate {}',
                    'chocolate.json' => json_encode([
                        'class' => 'Boots\Test\Dispenser\Chocolate',
                        'version' => '',
                    ]),
                ],
                'kitkat' => [ // versioned chocolate
                    'kitkat.php' => '<?php namespace Boots\Test\Dispenser; class KitKat_1_2 {}',
                    'kitkat.json' => json_encode([
                        'class' => 'Boots\Test\Dispenser\KitKat',
                        'version' => '1.2',
                    ]),
                ],
                'no-manifest' => ['no-manifest.php' => ''],
                'no-class' => [ // class not found
                    'no-class.php' => '<?php ',
                    'no-class.json' => json_encode([
                        'class' => 'Boots\Test\Dispenser\Nope',
                        'version' => '',
                    ]),
                ],
                'index' => [
                    'foo.php' => '<?php namespace Boots\Test\Dispenser; class Index {}',
                    'index.json' => json_encode([
                        'class' => 'Boots\Test\Dispenser\Index',
                        'version' => '',
                    ]),
                ],
            ],
        ]);

        $directory = vfsStream::url('boots/dispenser');
        $this->dispenser = new Dispenser($directory, new Locator, new Repository);
    }

    /** @test */
    public function it_should_be_an_implementation_of_a_contract()
    {
        $this->assertInstanceOf('Boots\Contract\DispenserContract', $this->dispenser);
    }

    /** @test */
    public function it_should_dispense_a_service()
    {
        $chocolate = $this->dispenser->dispense('chocolate');
        $this->assertEquals('Boots\Test\Dispenser\Chocolate', get_class($chocolate));
    }

    /** @test */
    public function it_should_dispense_a_versioned_service()
    {
        $kitkat = $this->dispenser->dispense('kitkat');
        $this->assertEquals('Boots\Test\Dispenser\KitKat_1_2', get_class($kitkat));
    }

    /** @test */
    public function it_should_dispense_a_service_with_a_custom_index_file()
    {
        $this->dispenser->setIndexFile('foo.php');
        $index = $this->dispenser->dispense('index');
        $this->assertEquals('Boots\Test\Dispenser\Index', get_class($index));
    }

    /** @test */
    public function it_should_throw_FileNotFoundException_if_manifest_file_does_not_exist()
    {
        $this->setExpectedException('Boots\Exception\FileNotFoundException');
        $this->dispenser->dispense('no-manifest');
    }

    /** @test */
    public function it_should_throw_FileNotFoundException_if_index_file_does_not_exist()
    {
        $this->setExpectedException('Boots\Exception\FileNotFoundException');
        $this->dispenser->dispense('no-file');
    }

    /** @test */
    public function it_should_throw_ClassNotFoundException_if_class_does_not_exist_after_loading_file()
    {
        $this->setExpectedException('Boots\Exception\ClassNotFoundException');
        $this->dispenser->dispense('no-class');
    }
}