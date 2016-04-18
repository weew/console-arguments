<?php

namespace tests\spec\Weew\ConsoleArguments;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Weew\ConsoleArguments\ArgumentsParser;
use Weew\ConsoleArguments\IArgumentsParser;

/**
 * @mixin ArgumentsParser
 */
class ArgumentsParserSpec extends ObjectBehavior {
    function it_is_initializable() {
        $this->shouldHaveType(ArgumentsParser::class);
    }

    function it_implements_icommand_parser() {
        $this->beAnInstanceOf(IArgumentsParser::class);
    }

    function it_parses_command_name() {
        $this->parse('group:command')
            ->shouldBe([
                'group:command'
            ]);
    }

    function it_parses_complex_strings() {
        $this->parse('command:name this too_here "and that" --is="a \" flag" --another=flag -qq "flag \"\' here"')
            ->shouldBe([
                'command:name',
                'this',
                'too_here',
                'and that',
                '--is',
                'a " flag',
                '--another',
                'flag',
                '-q',
                '-q',
                'flag "\' here'
            ]);
    }

    function it_allows_special_characters() {
        $this->parse('command:name this *:;%+.too here')
            ->shouldBe([
                'command:name',
                'this',
                '*:;%+.too',
                'here',
            ]);
    }

    function it_parses_arrays() {
        $this->parse(['arg1', 'arg2'])
            ->shouldBe(['arg1', 'arg2']);
    }

    function it_parses_arguments() {
        $this->parse('command arg1 arg2')
            ->shouldBe([
                'command', 'arg1', 'arg2'
            ]);
    }

    function it_parses_options() {
        $this->parse('--option value1 --option value2')
            ->shouldBe([
                '--option',
                'value1',
                '--option',
                'value2'
            ]);
    }

    function it_unquotes_strings() {
        $this->parse('"foo"')->shouldBe(['foo']);
        $this->parse("'foo'")->shouldBe(['foo']);
    }

    function it_normalizes_short_options() {
        $this->parse('-abc -de')
            ->shouldBe(['-a', '-b', '-c', '-d', '-e']);
    }

    function it_normalizes_equitation_signs() {
        $this->parse('--left=right')
            ->shouldBe([
                '--left', 'right'
            ]);
    }

    function it_ignores_multiple_equitation_signs() {
        $this->parse('--left="right=right"')
            ->shouldBe([
                '--left', 'right=right'
            ]);
    }

    function it_removes_empty_equitation_sides() {
        $this->parse('=')->shouldBe([]);
        $this->parse('left=')->shouldBe(['left']);
        $this->parse('=right')->shouldBe(['right']);
    }

    function it_parses_complex_command_properly() {
        $this->parse('group:command arg1 arg2 --option val1 --option=val2 arg3 -o val3 -o=val4 -a -bcd -ef')
            ->shouldBe([
                'group:command', 'arg1', 'arg2', '--option',
                'val1', '--option', 'val2', 'arg3', '-o', 'val3',
                '-o', 'val4', '-a', '-b', '-c', '-d', '-e', '-f'
            ]);
    }

    function it_groups_arguments() {
        $this->group(['arg1', 'arg2', '-x', '-f', 'val1', '--option1', 'val2', 'val3', '--option2', 'val4', '-f', 'val5', '--option1', 'val6'])
            ->shouldBe([
                'arguments' => ['arg1', 'arg2'],
                'optionsCount' => [
                    '-x' => 1,
                    '-f' => 2,
                    '--option1' => 2,
                    '--option2' => 1,
                ],
                '-x' => [],
                '-f' => ['val1', 'val5'],
                '--option1' => ['val2', 'val3', 'val6'],
                '--option2' => ['val4'],
            ]);
    }

    function it_can_tell_if_an_argument_is_a_option_name() {
        $this->isOptionAlias('')->shouldBe(false);
        $this->isOptionAlias(null)->shouldBe(false);
        $this->isOptionAlias('option')->shouldBe(false);
        $this->isOptionAlias('-option')->shouldBe(false);
        $this->isOptionAlias('--f')->shouldBe(false);
        $this->isOptionAlias('-f')->shouldBe(true);
        $this->isOptionAlias('-F')->shouldBe(true);
    }

    function it_can_tell_if_an_argument_is_a_option_alias() {
        $this->isOptionName('')->shouldBe(false);
        $this->isOptionName(null)->shouldBe(false);
        $this->isOptionName('alias')->shouldBe(false);
        $this->isOptionName('-alias')->shouldBe(false);
        $this->isOptionName('-a')->shouldBe(false);
        $this->isOptionName('--a')->shouldBe(false);
        $this->isOptionName('--alias')->shouldBe(true);
        $this->isOptionName('--al_ias')->shouldBe(true);
        $this->isOptionName('--NAME')->shouldBe(true);
    }

    function it_can_tell_if_an_argument_is_a_option_name_or_an_alias() {
        $this->isOptionNameOrAlias('')->shouldBe(false);
        $this->isOptionNameOrAlias(null)->shouldBe(false);
        $this->isOptionNameOrAlias('option')->shouldBe(false);
        $this->isOptionNameOrAlias('f')->shouldBe(false);
        $this->isOptionNameOrAlias('-f')->shouldBe(true);
        $this->isOptionNameOrAlias('-option')->shouldBe(false);
        $this->isOptionNameOrAlias('--option')->shouldBe(true);
        $this->isOptionNameOrAlias('-F')->shouldBe(true);
        $this->isOptionNameOrAlias('--NAME')->shouldBe(true);
    }

    function it_can_merge_name_and_alias() {
        $args = ['--option' => ['val1'], '-f' => ['val2']];
        $this->mergeNameAndAlias($args, '--option', '-f')
            ->shouldBe(['--option' => ['val1', 'val2']]);
    }
    
    function it_should_return_args_if_either_name_nor_alias_is_set() {
        $this->mergeNameAndAlias([], null, null)->shouldBe([]);
    }

    function it_can_merge_even_if_alias_is_absent_in_the_arguments() {
        $args = ['--option' => ['val1']];
        $this->mergeNameAndAlias($args, '--option', '-f')
            ->shouldBe(['--option' => ['val1']]);
    }

    function it_can_merge_even_if_name_is_absent_in_the_arguments() {
        $args = ['-f' => ['val2']];
        $this->mergeNameAndAlias($args, '--option', '-f')
            ->shouldBe(['--option' => ['val2']]);
    }

    function it_can_merge_even_if_name_and_alias_is_absent_in_the_arguments() {
        $args = ['arg1', 'arg2'];
        $this->mergeNameAndAlias($args, '--option', '-f')
            ->shouldBe(['arg1', 'arg2']);
    }

    function it_can_merge_even_option_name_is_absent() {
        $args = ['--option' => ['val1'], '-f' => ['val2']];
        $this->mergeNameAndAlias($args, null, '-f')
            ->shouldBe(['--option' => ['val1'], '-f' => ['val2']]);
    }

    function it_can_merge_even_option_alias_is_absent() {
        $args = ['--option' => ['val1'], '-f' => ['val2']];
        $this->mergeNameAndAlias($args, '--option', null)
            ->shouldBe(['-f' => ['val2'], '--option' => ['val1']]);
    }

    function it_merges_options_count_too() {
        $args = [
            '--option' => ['val1'],
            '-f' => ['val2'],
            'optionsCount' => [
                '--option' => 2,
                '-f' => 3,
            ],
        ];
        $this->mergeNameAndAlias($args, '--option', '-f')
            ->shouldBe([
                'optionsCount' => [
                    '--option' => 5,
                ],
                '--option' => ['val1', 'val2'],
            ]);
    }
}
