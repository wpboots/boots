<?php

use Boots\Event;

class EventTest extends PHPUnit_Framework_TestCase
{
    protected $event;

    public function setUp()
    {
        $this->event = new Event;
    }

    /** @test */
    public function it_should_be_an_implementation_of_a_contract()
    {
        $this->assertInstanceOf('Boots\Contract\EventContract', $this->event);
    }

    /** @test */
    public function fire_method_should_emit_all_event_listeners_for_a_given_key_and_return_results()
    {
        $this->event->add('foo', function ($event, $bar, $baz) {
            return $bar . $baz;
        });
        $this->event->add('foo', function ($event, $bar, $baz) {
            return "{$bar}.{$baz}";
        });
        $this->assertEquals(['barbaz', 'bar.baz'], $this->event->fire('foo', ['bar', 'baz']));
    }

    /** @test */
    public function actions_event_parameter_should_be_the_same_event_instance()
    {
        $this->event->add('foo', function ($event) {
            return $event;
        });
        $event = $this->event->fire('foo')[0];
        $this->assertSame($event, $this->event);
    }

    /** @test */
    public function actions_may_have_zero_parameters()
    {
        $this->event->add('foo', function () {
            return 'foo';
        });
        $this->assertEquals(['foo'], $this->event->fire('foo'));
    }

    /** @test */
    public function actions_event_parameter_should_allow_to_determine_the_event_name_by_method_call_and_property()
    {
        $this->event->add('foo', function ($event) {
            return $event->name();
        });
        $this->event->add('bar', function ($event) {
            $event->add('baz', function ($event) {
                return $event->name();
            });
            return $event->name();
        });
        $this->event->add('beep', function ($event) {
            return $event->name;
        });
        $this->assertEquals(['foo'], $this->event->fire('foo'));
        $this->assertEquals(['bar'], $this->event->fire('bar'));
        $this->assertEquals(['baz'], $this->event->fire('baz'));
        $this->assertEquals(['beep'], $this->event->fire('beep'));
    }

    /** @test */
    public function actions_event_parameter_should_allow_to_determine_the_event_name_as_boolean_if_key_is_passed()
    {
        $this->event->add('foo', function ($event) {
            return $event->name('foo');
        });
        $this->event->add('foo', function ($event) {
            return $event->name('bar');
        });
        $this->assertEquals([true, false], $this->event->fire('foo'));
    }

    /** @test */
    public function actions_event_parameter_should_allow_to_determine_the_event_params_by_method_call_and_property()
    {
        $this->event->add('foo', function ($event) {
            return $event->params();
        });
        $this->event->add('foo', function ($event) {
            return $event->params();
        });
        $this->event->add('bar', function ($event, $bar) {
            $event->add('baz', function ($event, $baz) {
                return $event->params();
            });
            return $event->params();
        });
        $this->event->add('beep', function ($event, $beep) {
            return $event->params;
        });
        $this->assertEquals([['foo'], ['foo']], $this->event->fire('foo', ['foo']));
        $this->assertEquals([['bar']], $this->event->fire('bar', ['bar']));
        $this->assertEquals([['baz']], $this->event->fire('baz', ['baz']));
        $this->assertEquals([['beep']], $this->event->fire('beep', ['beep']));
    }

    /** @test */
    public function actions_event_parameter_should_allow_to_determine_an_event_param_value_if_arg_key_is_passed()
    {
        $this->event->add('foo', function ($event, $bar) {
            return $event->params('bar');
        });
        $this->event->add('foo', function ($event, $bar) {
            return $event->params('bar');
        });
        $this->assertEquals(['baz', 'baz'], $this->event->fire('foo', ['bar' => 'baz']));
    }

    /** @test */
    public function delegate_method_should_allow_event_delegating()
    {
        $this->event->add('foo', function ($event) {
            return 'foo';
        });
        $this->event->add('foo', function ($event) {
            return 'bar';
        });
        $delegate = new Event;
        $delegate->add('foo', function ($event) {
            return 'delegate';
        });
        $this->event->delegate($delegate);
        $this->assertEquals(['foo', 'bar', 'delegate'], $this->event->fire('foo'));
    }

    /** @test */
    public function fire_method_should_throw_InfiniteRecursionException_if_firing_within_exactly_the_same_event()
    {
        $this->setExpectedException('Boots\Exception\InfiniteRecursionException');
        $this->event->add('foo', function ($event) {
            $event->fire('foo');
        });
        $this->event->fire('foo');
    }
}