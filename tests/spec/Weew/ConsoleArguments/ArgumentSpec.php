<?php

namespace tests\spec\Weew\ConsoleArguments;

use PhpSpec\ObjectBehavior;
use Weew\ConsoleArguments\Argument;
use Weew\ConsoleArguments\ArgumentType;
use Weew\ConsoleArguments\IArgument;

/**
 * @mixin Argument
 */
class ArgumentSpec extends ObjectBehavior {
    function let() {
        $this->beConstructedWith(
            ArgumentType::SINGLE,
            'default_name'
        );
    }

    function it_is_initializable() {
        $this->beAnInstanceOf(Argument::class);
    }

    function it_implements_iargument() {
        $this->shouldHaveType(IArgument::class);
    }

    function it_takes_a_type_trough_the_constructor() {
        $this->getType()->shouldBe(ArgumentType::SINGLE);
    }

    function it_takes_and_returns_type() {
        $this->setType(ArgumentType::SINGLE_OPTIONAL);
        $this->getType()->shouldBe(ArgumentType::SINGLE_OPTIONAL);
    }

    function it_can_tell_if_its_of_type_single() {
        $this->setType(ArgumentType::MULTIPLE);
        $this->isSingle()->shouldBe(false);
        $this->setType(ArgumentType::SINGLE);
        $this->isSingle()->shouldBe(true);
        $this->setType(ArgumentType::SINGLE_OPTIONAL);
        $this->isSingle()->shouldBe(true);
    }

    function it_can_tell_if_its_of_type_multiple() {
        $this->setType(ArgumentType::SINGLE);
        $this->isMultiple()->shouldBe(false);
        $this->setType(ArgumentType::MULTIPLE);
        $this->isMultiple()->shouldBe(true);
        $this->setType(ArgumentType::MULTIPLE_OPTIONAL);
        $this->isMultiple()->shouldBe(true);
    }

    function it_can_tell_if_it_is_required() {
        $this->setType(ArgumentType::SINGLE);
        $this->isRequired()->shouldBe(true);
        $this->setType(ArgumentType::SINGLE_OPTIONAL);
        $this->isRequired()->shouldBe(false);
        $this->setType(ArgumentType::MULTIPLE);
        $this->isRequired()->shouldBe(true);
        $this->setType(ArgumentType::MULTIPLE_OPTIONAL);
        $this->isRequired()->shouldBe(false);
    }

    function it_can_tell_if_it_is_optional() {
        $this->setType(ArgumentType::SINGLE);
        $this->isOptional()->shouldBe(false);
        $this->setType(ArgumentType::SINGLE_OPTIONAL);
        $this->isOptional()->shouldBe(true);
        $this->setType(ArgumentType::MULTIPLE);
        $this->isOptional()->shouldBe(false);
        $this->setType(ArgumentType::MULTIPLE_OPTIONAL);
        $this->isOptional()->shouldBe(true);
    }

    function it_takes_a_name_trough_the_constructor() {
        $this->getName()->shouldBe('default_name');
    }

    function it_takes_and_returns_name() {
        $this->setName('name');
        $this->getName()->shouldBe('name');
    }

    function it_takes_returns_different_default_values_based_on_type() {
        $this->setType(ArgumentType::SINGLE);
        $this->getDefaultValue()->shouldBe(null);
        $this->setType(ArgumentType::MULTIPLE);
        $this->getDefaultValue()->shouldBe([]);
    }

    function it_takes_and_returns_a_default_value() {
        $this->setDefaultValue('value');
        $this->getDefaultValue()->shouldBe('value');
    }

    function it_takes_and_returns_a_description() {
        $this->setDescription('description');
        $this->getDescription()->shouldBe('description');
    }

    function it_takes_and_returns_a_value() {
        $this->setValue('value');
        $this->getValue()->shouldBe('value');
    }

    function it_returns_the_default_value_if_the_value_is_null() {
        $this->setValue('value');
        $this->setDefaultValue('default');
        $this->getValue()->shouldBe('value');
        $this->setValue(null);
        $this->getValue()->shouldBe('default');
    }

    function it_can_tell_if_a_value_has_been_set() {
        $this->hasValue()->shouldBe(false);
        $this->setValue('value');
        $this->hasValue()->shouldBe(true);
        $this->setValue(null);
        $this->hasValue()->shouldBe(false);
    }

    function it_is_chainable_trough_set_name() {
        $this->setName('name')->shouldBe($this);
    }

    function it_is_chainable_trough_set_value() {
        $this->setValue('value')->shouldBe($this);
    }

    function it_is_chainable_trough_set_type() {
        $this->setType(ArgumentType::SINGLE)->shouldBe($this);
    }

    function it_is_chainable_trough_set_default_value() {
        $this->setDefaultValue('value')->shouldBe($this);
    }

    function it_is_chainable_trough_set_description() {
        $this->setDescription('description')->shouldBe($this);
    }
}
