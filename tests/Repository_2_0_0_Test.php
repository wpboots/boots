<?php

use Boots\Repository_2_0_0 as Repository;

class Repository_2_0_0_Test extends PHPUnit_Framework_TestCase
{
    protected $repo;

    protected $config;

    protected function setUp()
    {
        $this->config = [
            'one' => 'one_value',
            'two' => [
                'two_one' => 'two_one_value',
                'two_two' => 'two_two_value',
            ],
            'three' => [
                'three_one' => 'three_one_value',
                'three_two' => [
                    'three_two_one' => 'three_two_one_value',
                ],
                'three_three' => 'three_three_value',
            ],
        ];
        $this->repo = new Repository($this->config);
    }

    /** @test */
    public function it_should_be_instantiable_with_an_empty_array_by_default()
    {
        $repo = new Repository;
        $this->assertEquals([], $repo->all());
    }

    /** @test */
    public function it_should_be_instantiable_with_a_provided_array()
    {
        $this->assertEquals($this->config, $this->repo->all());
    }

    /** @test */
    public function it_should_return_the_entire_repository_as_an_array()
    {
        $this->assertEquals($this->config, $this->repo->all());
    }

    /** @test */
    public function it_should_determine_whether_a_given_key_exists()
    {
        $this->assertEquals(true, $this->repo->has('one'));
        $this->assertEquals(false, $this->repo->has('one.two'));
        $this->assertEquals(true, $this->repo->has('two'));
        $this->assertEquals(true, $this->repo->has('two.two_one'));
        $this->assertEquals(false, $this->repo->has('two.two_one.two_one_one'));
        $this->assertEquals(true, $this->repo->has('two.two_two'));
        $this->assertEquals(false, $this->repo->has('two.two_two.two_two_one'));
        $this->assertEquals(false, $this->repo->has('two.three'));
        $this->assertEquals(true, $this->repo->has('three.three_one'));
        $this->assertEquals(true, $this->repo->has('three.three_two'));
        $this->assertEquals(true, $this->repo->has('three.three_two.three_two_one'));
        $this->assertEquals(true, $this->repo->has('three.three_three'));
        $this->assertEquals(false, $this->repo->has('three.four'));
        $this->assertEquals(false, $this->repo->has('foo'));
        $this->assertEquals(false, $this->repo->has('foo.bar'));
        $this->assertEquals(false, $this->repo->has('foo.bar.baz'));
    }

    /** @test */
    public function it_should_return_the_value_for_a_given_key()
    {
        $this->assertEquals('one_value', $this->repo->get('one'));
        $this->assertEquals('one_value', $this->repo->get('one', 'one'));
        $this->assertEquals('two_one_value', $this->repo->get('two.two_one'));
        $this->assertEquals('two_one_value', $this->repo->get('two.two_one', 'two'));
        $this->assertEquals('three_two_one_value', $this->repo->get('three.three_two.three_two_one'));
        $this->assertEquals('three_two_one_value', $this->repo->get('three.three_two.three_two_one', 'three'));
    }

    /** @test */
    public function it_should_return_null_if_given_key_is_not_found()
    {
        $this->assertEquals(null, $this->repo->get('lorem'));
        $this->assertEquals(null, $this->repo->get('lorem.ipsum'));
        $this->assertEquals(null, $this->repo->get('one.two'));
    }

    /** @test */
    public function it_should_return_default_value_if_given_key_is_not_found()
    {
        $this->assertEquals([], $this->repo->get('lorem', []));
        $this->assertEquals(1, $this->repo->get('lorem.ipsum', 1));
        $this->assertEquals(0, $this->repo->get('one.two', 0));
    }

    /** @test */
    public function it_should_set_a_value_for_a_given_key()
    {
        $this->assertEquals(null, $this->repo->get('foo'));
        $this->repo->set('foo', 'bar');
        $this->assertEquals('bar', $this->repo->get('foo'));
        $this->assertEquals('bar', $this->repo->get('foo', 'foo'));

        $this->assertEquals(null, $this->repo->get('beep.boop'));
        $this->repo->set('beep.boop', 'baz');
        $this->assertEquals('baz', $this->repo->get('beep.boop'));
        $this->assertEquals('baz', $this->repo->get('beep.boop', 'boop'));

        $this->assertEquals('one_value', $this->repo->get('one'));
        $this->repo->set('one', 'foo');
        $this->assertEquals('foo', $this->repo->get('one'));
        $this->assertEquals('foo', $this->repo->get('one', 'one'));

        $this->assertEquals($this->config['two'], $this->repo->get('two'));
        $this->repo->set('two', ['foo' => 'bar', 'bar' => 'baz']);
        $this->assertEquals('two_one_value', $this->repo->get('two.two_one'));
        $this->assertEquals('two_two_value', $this->repo->get('two.two_two'));
        $this->assertEquals('bar', $this->repo->get('two.foo'));
        $this->assertEquals('bar', $this->repo->get('two.foo', 'foo'));
        $this->assertEquals('baz', $this->repo->get('two.bar'));
        $this->assertEquals('baz', $this->repo->get('two.bar', 'bar'));
    }

    /** @test */
    public function it_should_append_a_value_on_to_a_key_value()
    {
        $this->assertEquals(null, $this->repo->get('foo'));
        $this->repo->append('foo', 'foo');
        $this->assertEquals(['foo'], $this->repo->get('foo'));
        $this->repo->append('foo', 'bar');
        $this->assertEquals(['foo', 'bar'], $this->repo->get('foo'));
        $this->repo->append('foo', 'baz');
        $this->assertEquals(['foo', 'bar', 'baz'], $this->repo->get('foo'));
        
        $this->assertEquals('two_one_value', $this->repo->get('two.two_one'));
        $this->repo->append('two.two_one', 'two_one_value2');
        $this->assertEquals(['two_one_value', 'two_one_value2'], $this->repo->get('two.two_one'));

        $this->assertEquals(null, $this->repo->get('beep'));
        $this->repo->append('beep', ['boop' => 'baz']);
        $this->assertEquals('baz', $this->repo->get('beep.boop'));
        $this->repo->append('beep.boop', 'baap');
        $this->assertEquals(['baz', 'baap'], $this->repo->get('beep.boop'));

        $this->assertEquals('one_value', $this->repo->get('one'));
        $this->repo->append('one', 'foo');
        $this->assertEquals(['one_value', 'foo'], $this->repo->get('one'));
    }

    /** @test */
    public function it_should_allow_delegation()
    {
        $this->assertEquals($this->config, $this->repo->all());
        $config = [
            'a' => 'a_value',
            'b' => [
                'b_a' => 'b_a_value',
                'b_b' => 'b_b_value',
            ],
        ];
        $this->repo->delegate(new Repository(['a' => $config['a']]));
        $this->repo->delegate(new Repository(['b' => $config['b']]));

        $this->assertEquals('one_value', $this->repo->get('one'));
        $this->assertEquals('two_one_value', $this->repo->get('two.two_one'));
        $this->assertEquals('two_two_value', $this->repo->get('two.two_two'));
        $this->assertEquals('a_value', $this->repo->get('a'));
        $this->assertEquals('b_a_value', $this->repo->get('b.b_a'));
        $this->assertEquals('b_b_value', $this->repo->get('b.b_b'));

        $this->assertEquals(true, $this->repo->has('one'));
        $this->assertEquals(false, $this->repo->has('one.two'));
        $this->assertEquals(true, $this->repo->has('two'));
        $this->assertEquals(true, $this->repo->has('two.two_one'));
        $this->assertEquals(false, $this->repo->has('two.two_one.two_one_one'));
        $this->assertEquals(true, $this->repo->has('a'));
        $this->assertEquals(true, $this->repo->has('b'));
        $this->assertEquals(true, $this->repo->has('b.b_a'));
        $this->assertEquals(true, $this->repo->has('b.b_b'));
    }

    /** @test */
    public function it_should_return_the_entire_repository_with_delegates_as_an_array()
    {
        $this->repo->delegate(new Repository(['a' => 'b']));

        $all = $this->repo->all(true);
        $this->assertEquals(2, count($all));
        $this->assertEquals($this->config, $all[0]);
        $this->assertEquals(['a' => 'b'], $all[1]);

        $all = $this->repo->everything();
        $this->assertEquals(2, count($all));
        $this->assertEquals($this->config, $all[0]);
        $this->assertEquals(['a' => 'b'], $all[1]);
    }
}