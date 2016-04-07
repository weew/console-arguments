<?php

namespace tests\spec\Weew\ConsoleArguments;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Weew\ConsoleArguments\Exceptions\InvalidOptionValueException;
use Weew\ConsoleArguments\Exceptions\InvalidOptionAliasException;
use Weew\ConsoleArguments\Exceptions\InvalidOptionNameException;
use Weew\ConsoleArguments\Option;
use Weew\ConsoleArguments\OptionType;
use Weew\ConsoleArguments\IOption;

/**
 * @mixin Option
 */
class OptionSpec extends ObjectBehavior {
    function let() {
        $this->beConstructedWith(
            OptionType::BOOLEAN,
            '--default_name',
            '-a'
        );
    }

    function it_is_initializable() {
        $this->shouldHaveType(Option::class);
    }

    function it_implements_ioption() {
        $this->beAnInstanceOf(IOption::class);
    }

    function it_takes_a_name_trough_the_constructor() {
        $this->getName()->shouldBe('--default_name');
    }

    function it_takes_and_returns_a_name() {
        $this->setName('--name');
        $this->getName()->shouldBe('--name');
    }

    function it_accepts_an_empty_name() {
        $this->setName(null);
        $this->getName()->shouldBe(null);
    }

    function it_accepts_various_names() {
        $this->setName('--name');
        $this->setName('--na');
        $this->setName('--na_me');
        $this->setName('--na-me');
        $this->setName('--na-m-e');
    }

    function it_requires_a_valid_name_format() {
        $this->shouldThrow(InvalidOptionNameException::class)
            ->during('setName', ['name']);
        $this->shouldThrow(InvalidOptionNameException::class)
            ->during('setName', ['---name']);
    }

    function it_takes_an_alias_trough_the_constructor() {
        $this->getAlias()->shouldBe('-a');
    }

    function it_takes_and_returns_an_alias() {
        $this->setAlias('-a');
        $this->getAlias()->shouldBe('-a');
    }

    function it_accepts_an_empty_alias() {
        $this->setAlias(null);
        $this->getAlias()->shouldBe(null);
    }

    function it_requires_a_valid_alias_format() {
        $this->shouldThrow(InvalidOptionAliasException::class)
            ->during('setAlias', ['alias']);
        $this->shouldThrow(InvalidOptionAliasException::class)
            ->during('setAlias', ['--alias']);
        $this->shouldThrow(InvalidOptionAliasException::class)
            ->during('setAlias', ['-alias']);
    }

    function it_returns_name_or_alias() {
        $this->beConstructedWith(OptionType::SINGLE);
        $this->setAlias('-a');
        $this->getNameOrAlias()->shouldBe('-a');
        $this->setName('--name');
        $this->getNameOrAlias()->shouldBe('--name');
    }

    function it_takes_a_type_trough_the_constructor() {
        $this->getType()->shouldBe(OptionType::BOOLEAN);
    }

    function it_takes_and_returns_a_type() {
        $this->setType(OptionType::BOOLEAN);
        $this->getType()->shouldBe(OptionType::BOOLEAN);
    }

    function it_takes_and_returns_a_description() {
        $this->setDescription('description');
        $this->getDescription()->shouldBe('description');
    }

    function it_takes_and_returns_a_default_value() {
        $this->setDefaultValue('value');
        $this->getDefaultValue()->shouldBe('value');
    }

    function it_can_tell_if_it_has_a_certain_name_or_alias() {
        $this->setName('--name');
        $this->setAlias('-n');
        $this->hasNameOrAlias('--name')->shouldBe(true);
        $this->hasNameOrAlias('-n')->shouldBe(true);
        $this->hasNameOrAlias('another_name')->shouldBe(false);
    }

    function it_takes_and_returns_a_value() {
        $this->getValue()->shouldBe(false);
        $this->setValue(true);
        $this->getValue()->shouldBe(true);
    }

    function it_takes_null_value() {
        $this->setType(OptionType::BOOLEAN);
        $this->setValue(null);
    }

    function it_can_tell_if_it_has_a_value() {
        $this->setType(OptionType::BOOLEAN);
        $this->setValue(null);
        $this->hasValue()->shouldBe(false);
        $this->setValue(true);
        $this->hasValue()->shouldBe(true);
    }

    function it_throws_an_error_if_non_boolean_value_is_set() {
        $this->setType(OptionType::BOOLEAN);
        $this->shouldThrow(InvalidOptionValueException::class)
            ->during('setValue', [1]);
    }

    function it_throws_an_error_if_non_incremental_value_is_set() {
        $this->setType(OptionType::INCREMENTAL);
        $this->shouldThrow(InvalidOptionValueException::class)
            ->during('setValue', [true]);
    }

    function it_throws_an_error_if_non_scalar_value_is_set() {
        $this->setType(OptionType::SINGLE);
        $this->shouldThrow(InvalidOptionValueException::class)
            ->during('setValue', [[]]);
    }

    function it_throws_an_error_if_non_array_value_is_set() {
        $this->setType(OptionType::MULTIPLE);
        $this->shouldThrow(InvalidOptionValueException::class)
            ->during('setValue', [true]);
    }

    function it_can_tell_if_it_is_single() {
        $this->setType(OptionType::BOOLEAN);
        $this->isSingle()->shouldBe(false);
        $this->setType(OptionType::SINGLE);
        $this->isSingle()->shouldBe(true);
        $this->setType(OptionType::SINGLE_OPTIONAL);
        $this->isSingle()->shouldBe(true);
    }

    function it_can_tell_if_it_is_multiple() {
        $this->setType(OptionType::BOOLEAN);
        $this->isMultiple()->shouldBe(false);
        $this->setType(OptionType::MULTIPLE);
        $this->isMultiple()->shouldBe(true);
        $this->setType(OptionType::MULTIPLE_OPTIONAL);
        $this->isMultiple()->shouldBe(true);
    }

    function it_can_tell_if_it_is_boolean() {
        $this->setType(OptionType::INCREMENTAL);
        $this->isBoolean()->shouldBe(false);
        $this->setType(OptionType::INCREMENTAL);
        $this->isBoolean()->shouldBe(false);
    }

    function it_can_tell_if_it_is_incremental() {
        $this->setType(OptionType::BOOLEAN);
        $this->isIncremental()->shouldBe(false);
        $this->setType(OptionType::INCREMENTAL);
        $this->isIncremental()->shouldBe(true);
    }

    function it_can_tell_if_it_is_required() {
        $this->setType(OptionType::SINGLE);
        $this->isRequired()->shouldBe(true);
        $this->setType(OptionType::SINGLE_OPTIONAL);
        $this->isRequired()->shouldBe(false);
        $this->setType(OptionType::MULTIPLE);
        $this->isRequired()->shouldBe(true);
        $this->setType(OptionType::MULTIPLE_OPTIONAL);
        $this->isRequired()->shouldBe(false);
        $this->setType(OptionType::BOOLEAN);
        $this->isRequired()->shouldBe(false);
        $this->setType(OptionType::INCREMENTAL);
        $this->isRequired()->shouldBe(false);
    }

    function it_can_tell_if_it_is_optional() {
        $this->setType(OptionType::SINGLE);
        $this->isOptional()->shouldBe(false);
        $this->setType(OptionType::SINGLE_OPTIONAL);
        $this->isOptional()->shouldBe(true);
        $this->setType(OptionType::MULTIPLE);
        $this->isOptional()->shouldBe(false);
        $this->setType(OptionType::MULTIPLE_OPTIONAL);
        $this->isOptional()->shouldBe(true);
        $this->setType(OptionType::BOOLEAN);
        $this->isOptional()->shouldBe(true);
        $this->setType(OptionType::INCREMENTAL);
        $this->isOptional()->shouldBe(true);
    }

    function it_returns_different_default_values_for_different_option_types() {
        $this->setType(OptionType::BOOLEAN);
        $this->getDefaultValue()->shouldBe(false);
        $this->setType(OptionType::INCREMENTAL);
        $this->getDefaultValue()->shouldBe(0);
        $this->setType(OptionType::SINGLE);
        $this->getDefaultValue()->shouldBe(null);
        $this->setType(OptionType::MULTIPLE);
        $this->getDefaultValue()->shouldBe([]);
    }

    function it_uses_custom_default_values() {
        $this->setType(OptionType::BOOLEAN);
        $this->getDefaultValue()->shouldBe(false);
        $this->setDefaultValue('value');
        $this->getDefaultValue()->shouldBe('value');
    }

    function it_returns_a_default_value_if_the_value_is_null() {
        $this->setType(OptionType::INCREMENTAL);
        $this->setDefaultValue('value');
        $this->getValue()->shouldBe('value');
        $this->setValue(1);
        $this->getValue()->shouldBe(1);
    }

    function it_is_chainable_trough_set_name() {
        $this->setName('--name')->shouldBe($this);
    }

    function it_is_chainable_trough_set_alias() {
        $this->setAlias('-a')->shouldBe($this);
    }

    function it_is_chainable_trough_set_description() {
        $this->setDescription('description')->shouldBe($this);
    }

    function it_is_chainable_trough_set_value() {
        $this->setValue(true)->shouldBe($this);
    }

    function it_is_chainable_trough_set_default_value() {
        $this->setDefaultValue('default_value')->shouldBe($this);
    }

    function it_is_chainable_trough_set_type() {
        $this->setType(OptionType::SINGLE)->shouldBe($this);
    }
}
