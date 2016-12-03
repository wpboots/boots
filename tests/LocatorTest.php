<?php

use Boots\Locator;

class LocatorTest extends PHPUnit_Framework_TestCase
{
    protected $locator;

    public function setUp()
    {
        $this->locator = new Locator;
    }

    /** @test */
    public function it_should_load_a_class_from_a_file_if_class_not_found()
    {
        $fqcn = $this->locator->locate(__DIR__, 'Foo\Bar');
        $this->assertTrue('Foo\Bar', $fqcn);
    }

    /** @test */
    public function it_should_load_a_versioned_class_from_a_file_if_class_not_found()
    {
        $fqcn = $this->locator->locate(__DIR__, 'Foo\Bar', '0.1');
        $this->assertTrue('Foo\Bar_0_1', $fqcn);
    }
}