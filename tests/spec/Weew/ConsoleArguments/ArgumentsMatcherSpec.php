<?php

namespace tests\spec\Weew\ConsoleArguments;

use PhpSpec\ObjectBehavior;
use Weew\ConsoleArguments\Argument;
use Weew\ConsoleArguments\ArgumentsParser;
use Weew\ConsoleArguments\ArgumentType;
use Weew\ConsoleArguments\Command;
use Weew\ConsoleArguments\ArgumentsMatcher;
use Weew\ConsoleArguments\Exceptions\AmbiguousCommandException;
use Weew\ConsoleArguments\Exceptions\CommandNotFoundException;
use Weew\ConsoleArguments\Exceptions\InvalidOptionValueException;
use Weew\ConsoleArguments\Exceptions\MissingArgumentValueException;
use Weew\ConsoleArguments\Exceptions\MissingCommandNameException;
use Weew\ConsoleArguments\Exceptions\MissingOptionValueException;
use Weew\ConsoleArguments\Exceptions\TooManyArgumentValuesException;
use Weew\ConsoleArguments\Exceptions\UnknownOptionException;
use Weew\ConsoleArguments\Option;
use Weew\ConsoleArguments\OptionType;
use Weew\ConsoleArguments\ICommand;
use Weew\ConsoleArguments\IArgumentsMatcher;

/**
 * @mixin ArgumentsMatcher
 */
class ArgumentsMatcherSpec extends ObjectBehavior {
    function let() {
        $this->beConstructedWith(new ArgumentsParser());
    }

    function it_is_initializable() {
        $this->shouldHaveType(ArgumentsMatcher::class);
    }

    function it_implements_icommand_matcher() {
        $this->beAnInstanceOf(IArgumentsMatcher::class);
    }

    function it_can_construct_without_parser() {
        $this->beConstructedWith();
        $this->matchCommandName(['arguments' => ['command']]);
    }

    function it_matches_command_name() {
        $args = ['arguments' => ['command', 'arg'], '-f'];
        $result = $this->matchCommandName($args);
        $result[0]->shouldBe('command');
        $result[1]->shouldBe(['arguments' => ['arg'], '-f']);
    }

    function it_throws_an_error_if_command_name_is_not_valid() {
        $args = ['-f', 'arg'];
        $this->shouldThrow(MissingCommandNameException::class)
            ->during('matchCommandName', [$args]);
    }

    function it_throws_an_error_if_command_name_is_not_set() {
        $args = [];
        $this->shouldThrow(MissingCommandNameException::class)
            ->during('matchCommandName', [$args]);
    }

    function it_throws_an_error_if_command_name_is_null() {
        $args = [null];
        $this->shouldThrow(MissingCommandNameException::class)
            ->during('matchCommandName', [$args]);
    }

    function it_finds_a_command() {
        $commands = [
            new Command('name1'),
            new Command('name2'),
            new Command('name3'),
        ];
        $command = $this->findCommand($commands, 'name3');
        $command->shouldHaveType(ICommand::class);
        $command->getName()->shouldBe('name3');
    }

    function it_throws_an_exception_if_command_cant_be_found() {
        $commands = [
            new Command('name1'),
            new Command('name2'),
        ];
        $this->shouldThrow(CommandNotFoundException::class)
            ->during('findCommand', [$commands, 'name3']);
    }

    function it_throws_an_error_if_command_name_is_ambiguous() {
        $commands = [
            new Command('name1'),
            new Command('name2'),
        ];
        $this->shouldThrow(AmbiguousCommandException::class)
            ->during('findCommand', [$commands, 'name']);
    }

    function it_compares_a_command_name() {
        $this->compareCommandName('some:name', 'some:name')->shouldBe(true);
        $this->compareCommandName('some:name', 'so:na')->shouldBe(true);
        $this->compareCommandName('some:name', 's:n')->shouldBe(true);
        $this->compareCommandName('some:name', 's:')->shouldBe(true);
        $this->compareCommandName('some:name', 'ome:name')->shouldBe(false);
        $this->compareCommandName('some:name', 'some:ame')->shouldBe(false);
        $this->compareCommandName('some:name', 'some:name:here')->shouldBe(false);
    }

    function it_matches_an_argument() {
        $argument = new Argument(ArgumentType::SINGLE, 'argument');
        $args = ['arguments' => ['arg1', 'arg2']];
        $this->matchArgument($argument, $args)
            ->shouldBe(['arguments' => ['arg2']]);
        expect($argument->getValue())->shouldBe('arg1');
    }

    function it_matches_an_argument_of_type_multiple() {
        $argument = new Argument(ArgumentType::MULTIPLE, 'argument');
        $args = ['arguments' => ['arg1', 'arg2']];
        $this->matchArgument($argument, $args)
            ->shouldBe(['arguments' => []]);
        expect($argument->getValue())->shouldBe(['arg1', 'arg2']);
    }

    function it_does_not_throw_an_error_for_a_missing_optional_argument() {
        $argument = new Argument(ArgumentType::SINGLE_OPTIONAL, 'argument');
        $args = [];
        $this->matchArgument($argument, $args)->shouldBe([]);
        expect($argument->getValue())->shouldBe(null);
    }

    function it_does_not_throw_an_error_for_a_missing_optional_argument_of_type_multiple() {
        $argument = new Argument(ArgumentType::MULTIPLE_OPTIONAL, 'argument');
        $args = [];
        $this->matchArgument($argument, $args)->shouldBe([]);
        expect($argument->getValue())->shouldBe([]);
    }

    function it_should_throw_an_error_for_a_missing_required_argument() {
        $argument = new Argument(ArgumentType::SINGLE, 'argument');
        $args = [];
        $this->shouldThrow(MissingArgumentValueException::class)
            ->during('matchArgument', [$argument, $args]);
    }

    function it_should_throw_an_error_for_a_missing_required_argument_of_type_multiple() {
        $argument = new Argument(ArgumentType::MULTIPLE, 'argument');
        $args = [];
        $this->shouldThrow(MissingArgumentValueException::class)
            ->during('matchArgument', [$argument, $args]);
    }

    function it_matches_an_option() {
        $option = new Option(OptionType::SINGLE, '--option', null);
        $args = ['arg1', '--option' => ['option1']];
        $this->matchOption($option, $args)
            ->shouldBe(['arg1']);
        expect($option->getValue())->shouldBe('option1');
    }

    function it_matches_a_option_by_alias() {
        $option = new Option(OptionType::SINGLE, '--option', '-f');
        $args = ['arg1', '-f' => ['option1']];
        $this->matchOption($option, $args)
            ->shouldBe(['arg1']);
        expect($option->getValue())->shouldBe('option1');
    }

    function it_matches_an_optional_option_without_arguments() {
        $option = new Option(OptionType::SINGLE_OPTIONAL, '--option', '-f');
        $args = ['arg1', 'arg2'];
        $this->matchOption($option, $args)
            ->shouldBe(['arg1', 'arg2']);
        expect($option->getValue())->shouldBe(null);
    }

    function it_throws_an_error_if_a_required_option_is_not_set() {
        $option = new Option(OptionType::SINGLE, '--option', null);
        $args = ['arg1'];
        $this->shouldThrow(MissingOptionValueException::class)
            ->during('matchOption', [$option, $args]);
    }

    function it_throws_an_error_if_a_required_option_is_set_without_an_argument() {
        $option = new Option(OptionType::SINGLE, '--option', null);
        $args = ['arg1', '--option' => []];
        $this->shouldThrow(MissingOptionValueException::class)
            ->during('matchOption', [$option, $args]);
    }

    function it_takes_the_last_value_if_an_option_of_type_single_receives_multiple_values() {
        $option = new Option(OptionType::SINGLE, '--option', null);
        $args = ['arg1', '--option' => ['option1', 'option2']];
        $this->matchOption($option, $args)->shouldBe(['arg1']);
        it($option->getValue())->shouldBe('option2');
    }

    function it_matches_a_required_option_of_type_multiple_with_multiple_arguments() {
        $option = new Option(OptionType::MULTIPLE, '--option', null);
        $args = ['arg1', '--option' => ['option1', 'option2']];
        $this->matchOption($option, $args)->shouldBe(['arg1']);
        expect($option->getValue())->shouldBe(['option1', 'option2']);
    }

    function it_matches_a_required_option_of_type_multiple_with_a_single_argument() {
        $option = new Option(OptionType::MULTIPLE, '--option', null);
        $args = ['arg1', '--option' => ['option1']];
        $this->matchOption($option, $args)->shouldBe(['arg1']);
        expect($option->getValue())->shouldBe(['option1']);
    }

    function it_throws_an_error_if_a_required_option_of_type_multiple_is_set_without_arguments() {
        $option = new Option(OptionType::MULTIPLE, '--option', null);
        $args = ['arg1', 'arg2'];
        $this->shouldThrow(MissingOptionValueException::class)
            ->during('matchOption', [$option, $args]);
    }

    function it_matches_an_optional_option_of_type_multiple_with_no_arguments() {
        $option = new Option(OptionType::MULTIPLE_OPTIONAL, '--option', null);
        $args = ['arg1', 'arg2'];
        $this->matchOption($option, $args)->shouldBe(['arg1', 'arg2']);
    }

    function it_matches_a_required_option_of_type_multiple_by_alias() {
        $option = new Option(OptionType::MULTIPLE, '--option', '-f');
        $args = ['arg1', '--option' => ['option1', 'option2'], '-f' => ['option3', 'option4']];
        $this->matchOption($option, $args)->shouldBe(['arg1']);
        expect($option->getValue())->shouldBe(['option1', 'option2', 'option3', 'option4']);
    }

    function it_matches_a_boolean_option_by_name() {
        $option = new Option(OptionType::BOOLEAN, '--option', '-f');
        $args = ['arg1', 'arg2', '--option' => []];
        $this->matchOption($option, $args)
            ->shouldBe(['arg1', 'arg2']);
        expect($option->getValue())->shouldBe(true);
    }

    function it_matches_a_boolean_option_by_alias() {
        $option = new Option(OptionType::BOOLEAN, '--option', '-f');
        $args = ['arg1', 'arg2', '-f' => []];
        $this->matchOption($option, $args)
            ->shouldBe(['arg1', 'arg2']);
        expect($option->getValue())->shouldBe(true);
    }

    function it_throws_no_errors_if_a_boolean_option_hasnt_been_set() {
        $option = new Option(OptionType::BOOLEAN, '--option', '-f');
        $args = ['arg1', 'arg2'];
        $this->matchOption($option, $args)
            ->shouldBe(['arg1', 'arg2']);
        expect($option->getValue())->shouldBe(false);
    }

    function it_reads_boolean_option_arguments() {
        $option = new Option(OptionType::BOOLEAN, '--option', '-f');

        $args = ['arg1', 'arg2', '-f' => [0, 'true']];
        $this->matchOption($option, $args)->shouldBe(['arg1', 'arg2']);
        it($option->getValue())->shouldBe(true);

        $args = ['arg1', 'arg2', '-f' => [0, 'false']];
        $this->matchOption($option, $args)->shouldBe(['arg1', 'arg2']);
        it($option->getValue())->shouldBe(false);

        $args = ['arg1', 'arg2', '-f' => [0, '1']];
        $this->matchOption($option, $args)->shouldBe(['arg1', 'arg2']);
        it($option->getValue())->shouldBe(true);

        $args = ['arg1', 'arg2', '-f' => [0, '0']];
        $this->matchOption($option, $args)->shouldBe(['arg1', 'arg2']);
        it($option->getValue())->shouldBe(false);
    }

    function it_throws_an_error_if_boolean_option_receives_invalid_value() {
        $option = new Option(OptionType::BOOLEAN, '--option', '-f');
        $args = ['arg1', 'arg2', '-f' => [0, 'invalid']];
        $this->shouldThrow(InvalidOptionValueException::class)
            ->during('matchOption', [$option, $args]);
    }

    function it_matches_an_incremental_option_by_name() {
        $option = new Option(OptionType::INCREMENTAL, '--option', '-f');
        $args = ['arg1', 'arg2', '--option' => [3]];
        $this->matchOption($option, $args)
            ->shouldBe(['arg1', 'arg2']);
        expect($option->getValue())->shouldBe(3);
    }

    function it_matches_an_incremental_option_by_alias() {
        $option = new Option(OptionType::INCREMENTAL, '--option', '-f');
        $args = ['arg1', 'arg2', '-f' => [3]];
        $this->matchOption($option, $args)
            ->shouldBe(['arg1', 'arg2']);
        expect($option->getValue())->shouldBe(3);
    }

    function it_matches_an_incremental_option_by_name_and_alias() {
        $option = new Option(OptionType::INCREMENTAL, '--option', '-f');
        $args = ['arg1', 'arg2', '--option' => [3]];
        $this->matchOption($option, $args)
            ->shouldBe(['arg1', 'arg2']);
        expect($option->getValue())->shouldBe(3);
    }

    function it_reads_values_of_incremental_options() {
        $option = new Option(OptionType::INCREMENTAL, '--option', '-f');
        $args = ['arg1', 'arg2', '--option' => ['arg', '-1']];
        $this->matchOption($option, $args)
            ->shouldBe(['arg1', 'arg2']);
        it($option->getValue())->shouldBe(-1);

        $args = ['arg1', 'arg2', '-f' => ['3']];
        $this->matchOption($option, $args)
            ->shouldBe(['arg1', 'arg2']);
        it($option->getValue())->shouldBe(3);
    }

    function it_throws_no_errors_if_the_incremental_option_hasnt_been_set() {
        $option = new Option(OptionType::INCREMENTAL, '--option', '-f');
        $args = ['arg1', 'arg2'];
        $this->matchOption($option, $args)
            ->shouldBe(['arg1', 'arg2']);
        expect($option->getValue())->shouldBe(0);
    }

    function it_matches_commands() {
        $command1 = new Command('name1');
        $command2 = new Command('name2');
        $commands = [$command1, $command2];
        $args = ['name2'];
        $command = $this->matchCommands($commands, $args);
        $command->shouldBe($command2);
    }

    function it_matches_a_command_with_arguments() {
        $arg1 = new Argument(ArgumentType::SINGLE, 'arg1');
        $arg2 = new Argument(ArgumentType::SINGLE, 'arg2');
        $arg3 = new Argument(ArgumentType::SINGLE_OPTIONAL, 'arg3');

        $args = ['arguments' => ['arg1', 'arg2']];

        $command = new Command('name');
        $command->addArguments([$arg1, $arg2, $arg3]);

        $this->matchCommand($command, $args)->shouldBe(['arguments' => []]);
        it($arg1->getValue())->shouldBe('arg1');
        it($arg1->hasValue())->shouldBe(true);
        it($arg2->getValue())->shouldBe('arg2');
        it($arg2->hasValue())->shouldBe(true);
        it($arg3->hasValue())->shouldBe(false);
    }

    function it_throws_an_error_if_command_receives_too_many_arguments() {
        $arg1 = new Argument(ArgumentType::SINGLE, 'arg1');

        $args = ['arguments' => ['arg1', 'arg2', 'arg3']];

        $command = new Command('name');
        $command->addArguments([$arg1]);

        $this->shouldThrow(TooManyArgumentValuesException::class)
            ->during('matchCommand', [$command, $args]);
    }

    function it_matches_a_command_with_options_with_values() {
        $option1 = new Option(OptionType::SINGLE, '--aa', '-a');
        $option2 = new Option(OptionType::SINGLE, '--bb', null);
        $option3 = new Option(OptionType::SINGLE_OPTIONAL, '--dd', '-d');
        $option4 = new Option(OptionType::SINGLE, null, '-c');
        $option5 = new Option(OptionType::MULTIPLE, '--ee', null);
        $option6 = new Option(OptionType::MULTIPLE_OPTIONAL, null, '-f');

        $args = ['-a' => ['val1'], '--bb' => ['val2'], '-c' => ['val3'], '--ee' => ['val4'], '-f' => ['val5', 'val6']];

        $command = new Command('name');
        $command->addOptions([$option1, $option2, $option3, $option4, $option5, $option6]);

        $this->matchCommand($command, $args);
        it($option1->hasValue())->shouldBe(true);
        it($option1->getValue())->shouldBe('val1');
        it($option2->hasValue())->shouldBe(true);
        it($option2->getValue())->shouldBe('val2');
        it($option3->hasValue())->shouldBe(false);
        it($option4->hasValue())->shouldBe(true);
        it($option4->getValue())->shouldBe('val3');
        it($option5->hasValue())->shouldBe(true);
        it($option5->getValue())->shouldBe(['val4']);
        it($option6->hasValue())->shouldBe(true);
        it($option6->getValue())->shouldBe(['val5', 'val6']);
    }

    function it_matches_a_command_with_options_without_values() {
        $option1 = new Option(OptionType::BOOLEAN, '--aa', null);
        $option2 = new Option(OptionType::BOOLEAN, '--bb', null);
        $option3 = new Option(OptionType::BOOLEAN, null, '-c');
        $option4 = new Option(OptionType::INCREMENTAL, null, '-d');
        $option5 = new Option(OptionType::INCREMENTAL, '--ee', '-e');

        $args = ['--aa' => [], '-c' => [], '-d' => [], '-e' => [], 'optionsCount' => ['-d' => 4, '-e' => 5]];

        $command = new Command('name');
        $command->addOptions([$option1, $option2, $option3, $option4, $option5]);

        $this->matchCommand($command, $args);
        it($option1->getValue())->shouldBe(true);
        it($option2->getValue())->shouldBe(false);
        it($option3->getValue())->shouldBe(true);
        it($option4->getValue())->shouldBe(4);
        it($option5->getValue())->shouldBe(5);
    }

    function it_throws_an_error_if_command_receives_unknown_options_in_strict_mode() {
        $command = new Command('name');
        $this->shouldThrow(UnknownOptionException::class)
            ->during('matchCommand', [$command, ['--option' => ['value']]]);
    }

    function it_does_not_throw_an_error_if_command_receives_unknown_options_in_non_strict_mode() {
        $command = new Command('name');
        $this->matchCommand($command, ['--option' => ['value']], false);
    }
}
