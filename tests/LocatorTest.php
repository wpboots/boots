<?php

use Boots\Locator;
use org\bovigo\vfs\vfsStream;

class LocatorTest extends PHPUnit_Framework_TestCase
{
    protected $locator;

    public function setUp()
    {
        vfsStream::setup('boots', null, [
            'TestFooBar.php' => '<?php namespace Test\Foo; class Bar {}',
            'TestFooBar_1_0.php' => '<?php namespace Test\Foo; class Bar_1_0 {}',
            'TestFooBar_1_0_.php' => '<?php namespace Test\Foo; class Bar_1_0_ {}',
        ]);

        $this->locator = new Locator;
    }

    /** @test */
    public function it_should_load_a_class_from_a_file_if_class_does_not_exist()
    {
        $filepath = vfsStream::url('boots/TestFooBar.php');
        $fqcn = $this->locator->locate($filepath, 'Test\Foo\Bar');
        $this->assertEquals('Test\Foo\Bar', $fqcn);
    }

    /** @test */
    public function it_should_load_a_versioned_class_from_a_file_if_class_does_not_exist()
    {
        $filepath = vfsStream::url('boots/TestFooBar_1_0.php');
        $fqcn = $this->locator->locate($filepath, 'Test\Foo\Bar', '1.0');
        $this->assertEquals('Test\Foo\Bar_1_0', $fqcn);
    }

    /** @test */
    public function it_should_return_versioned_fqcn_without_loading_file_if_class_exists()
    {
        $filepath = vfsStream::url('boots/TestFooBar_1_0.php');
        $this->locator->locate($filepath, 'Test\Foo\Bar', '1.0');

        $filepath = vfsStream::url('boots/TestFooBar_1_0_.php');
        $fqcn = $this->locator->locate($filepath, 'Test\Foo\Bar', '1.0');
        $this->assertEquals('Test\Foo\Bar_1_0', $fqcn);
        $this->assertTrue(!class_exists('Test\Foo\Bar_1_0_'));
    }

    /** @test */
    public function it_should_throw_FileNotFoundException_if_file_does_not_exist_when_class_does_not_exist()
    {
        $this->setExpectedException('Boots\Exception\FileNotFoundException');
        $filepath = vfsStream::url('boots/FileNotFound.php');
        $this->locator->locate($filepath, 'Test\File\Not\Found');
    }
}