<?php

use Boots\Dispenser;
use org\bovigo\vfs\vfsStream;

class DispenserTest extends PHPUnit_Framework_TestCase
{
    protected $dispenser;

    public function setUp()
    {
        vfsStream::setup('boots', null, [
            'dispenser' => [
                'chocolate' => [
                    'chocolate.php' => '<?php namespace Boots\Test\Dispenser; class Chocolate {}'
                ]
            ],
        ]);

        $directory = vfsStream::url('boots/dispenser');
        $this->dispenser = new Dispenser($directory);
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
        $this->assertInstanceOf('Boots\Test\Dispenser\Chocolate', $chocolate);
    }
}