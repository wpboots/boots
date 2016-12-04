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
                ]
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
}