<?php

use Boots\Locator;
use org\bovigo\vfs\vfsStream;

class LocatorTest extends PHPUnit_Framework_TestCase
{
    protected $locator;

    public function setUp()
    {
        vfsStream::setup('boots', null, [
            'TestLocatorBar.php' => '<?php namespace Boots\Test\Locator; class Bar {}',
            'TestLocatorBar_1_0.php' => '<?php namespace Boots\Test\Locator; class Bar_1_0 {}',
            'TestLocatorBar_1_0_.php' => '<?php namespace Boots\Test\Locator; class Bar_1_0_ {}',
            'TestLocatorBar_404.php' => '<?php namespace Boots\Test\Locator; class WrongClass {}',
        ]);

        $this->locator = new Locator;
    }

    /** @test */
    public function it_should_load_a_class_from_a_file_if_class_does_not_exist()
    {
        $filepath = vfsStream::url('boots/TestLocatorBar.php');
        $fqcn = $this->locator->locate($filepath, 'Boots\Test\Locator\Bar');
        $this->assertEquals('Boots\Test\Locator\Bar', $fqcn);
    }

    /** @test */
    public function it_should_load_a_versioned_class_from_a_file_if_class_does_not_exist()
    {
        $filepath = vfsStream::url('boots/TestLocatorBar_1_0.php');
        $fqcn = $this->locator->locate($filepath, 'Boots\Test\Locator\Bar', '1.0');
        $this->assertEquals('Boots\Test\Locator\Bar_1_0', $fqcn);
    }

    /** @test */
    public function it_should_return_versioned_fqcn_without_loading_file_if_class_exists()
    {
        $filepath = vfsStream::url('boots/TestLocatorBar_1_0.php');
        $this->locator->locate($filepath, 'Boots\Test\Locator\Bar', '1.0');

        $filepath = vfsStream::url('boots/TestLocatorBar_1_0_.php');
        $fqcn = $this->locator->locate($filepath, 'Boots\Test\Locator\Bar', '1.0');
        $this->assertEquals('Boots\Test\Locator\Bar_1_0', $fqcn);
        $this->assertTrue(!class_exists('Boots\Test\Locator\Bar_1_0_'));
    }

    /** @test */
    public function it_should_throw_FileNotFoundException_if_file_does_not_exist_when_class_does_not_exist()
    {
        $this->setExpectedException('Boots\Exception\FileNotFoundException');
        $filepath = vfsStream::url('boots/FileNotFound.php');
        $this->locator->locate($filepath, 'Boots\Test\File\Not\Found');
    }

    /** @test */
    public function it_should_throw_ClassNotFoundException_if_class_does_not_exist_after_loading_file()
    {
        $this->setExpectedException('Boots\Exception\ClassNotFoundException');
        $filepath = vfsStream::url('boots/TestLocatorBar_404.php');
        $this->locator->locate($filepath, 'Boots\Test\Locator\Bar\Baz');
    }
}