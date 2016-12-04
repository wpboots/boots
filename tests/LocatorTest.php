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
        ]);

        $this->locator = new Locator;
    }

    /** @test */
    public function it_should_load_a_class_from_a_file_if_class_not_found()
    {
        $filepath = vfsStream::url('boots/TestFooBar.php');
        $fqcn = $this->locator->locate($filepath, 'Test\Foo\Bar');
        $this->assertEquals('Test\Foo\Bar', $fqcn);
    }

    /** @test */
    public function it_should_load_a_versioned_class_from_a_file_if_class_not_found()
    {
        $filepath = vfsStream::url('boots/TestFooBar_1_0.php');
        $fqcn = $this->locator->locate($filepath, 'Test\Foo\Bar', '1.0');
        $this->assertEquals('Test\Foo\Bar_1_0', $fqcn);
    }
}