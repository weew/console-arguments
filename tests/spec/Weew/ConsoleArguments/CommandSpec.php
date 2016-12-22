<?php

namespace tests\spec\Weew\ConsoleArguments;

use PhpSpec\ObjectBehavior;
use Weew\ConsoleArguments\Argument;
use Weew\ConsoleArguments\ArgumentType;
use Weew\ConsoleArguments\Command;
use Weew\ConsoleArguments\Exceptions\ArgumentNotFoundException;
use Weew\ConsoleArguments\Exceptions\OptionNotFoundException;
use Weew\ConsoleArguments\Option;
use Weew\ConsoleArguments\OptionType;
use Weew\ConsoleArguments\ICommand;

/**
 * @mixin Command
 */
class CommandSpec extends ObjectBehavior {
    function let() {
        $this->beConstructedWith(
            'default_name',
            'default_description'
        );
    }

    function it_is_initializable() {
        $this->shouldHaveType(Command::class);
    }

    function it_implements_icommand() {
        $this->beAnInstanceOf(ICommand::class);
    }

    function it_takes_a_name_trough_the_constructor() {
        $this->getName()->shouldBe('default_name');
    }

    function it_takes_and_returns_a_name() {
        $this->setName('name');
        $this->getName()->shouldBe('name');
    }

    function it_is_chainable_trough_set_name() {
        $this->setName('name')->shouldBe($this);
    }

    function it_takes_a_description_trough_the_constructor() {
        $this->getDescription()->shouldBe('default_description');
    }

    function it_takes_and_returns_a_description() {
        $this->setDescription('description');
        $this->getDescription()->shouldBe('description');
    }

    function it_is_chainable_trough_set_description() {
        $this->setDescription('description')->shouldBe($this);
    }

    function it_takes_and_returns_help() {
        $this->setHelp('help');
        $this->getHelp()->shouldBe('help');
    }

    function it_is_chainable_trough_set_help() {
        $this->setHelp('help')->shouldBe($this);
    }

    function it_takes_and_returns_arguments() {
        $argument = new Argument(ArgumentType::SINGLE, 'name');
        $this->setArguments([$argument]);
        $this->getArguments()->shouldBe([$argument]);
    }

    function it_adds_multiple_arguments() {
        $argument1 = new Argument(ArgumentType::SINGLE, 'name1');
        $argument2 = new Argument(ArgumentType::SINGLE, 'name2');
        $this->addArguments([$argument1]);
        $this->addArguments([$argument2]);
        $this->getArguments()->shouldBe([$argument1, $argument2,]);
    }

    function it_adds_an_argument() {
        $argument1 = new Argument(ArgumentType::SINGLE, 'name1');
        $argument2 = new Argument(ArgumentType::SINGLE, 'name2');
        $this->addArgument($argument1);
        $this->addArgument($argument2);
        $this->getArguments()->shouldBe([$argument1, $argument2,]);
    }

    function it_registers_an_argument() {
        $argument = $this->argument(ArgumentType::SINGLE, 'name');
        $this->getArguments()->shouldHaveCount(1);
        $argument->getName()->shouldBe('name');
        $argument->getType()->shouldBe(ArgumentType::SINGLE);
    }

    function it_takes_and_returns_options() {
        $option = new Option(OptionType::SINGLE, '--name');
        $this->setOptions([$option]);
        $this->getOptions()->shouldBe([$option,]);
    }

    function it_adds_multiple_options() {
        $option1 = new Option(OptionType::SINGLE, '--name1');
        $option2 = new Option(OptionType::SINGLE, '--name2');
        $this->addOptions([$option1]);
        $this->addOptions([$option2]);
        $this->getOptions()->shouldBe([$option1, $option2,]);
    }

    function it_adds_a_option() {
        $option1 = new Option(OptionType::SINGLE, '--name1');
        $option2 = new Option(OptionType::SINGLE, '--name2');
        $this->addOption($option1);
        $this->addOption($option2);
        $this->getOptions()->shouldBe([$option1, $option2,]);
    }

    function it_registers_a_option() {
        $option = $this->option(OptionType::INCREMENTAL, '--name', '-a');
        $this->getOptions()->shouldHaveCount(1);
        $option->getName()->shouldBe('--name');
        $option->getAlias()->shouldBe('-a');
        $option->getType()->shouldBe(OptionType::INCREMENTAL);
    }

    function it_finds_an_argument_by_name() {
        $arg1 = new Argument(ArgumentType::SINGLE, 'name1');
        $arg2 = new Argument(ArgumentType::SINGLE, 'name2');

        $this->addArguments([$arg1, $arg2]);
        $this->findArgument('name2')->shouldBe($arg2);
    }

    function it_throws_an_error_if_argument_cant_be_found() {
        $this->shouldThrow(ArgumentNotFoundException::class)
            ->during('findArgument', ['name']);
    }

    function it_finds_an_option_by_name() {
        $opt1 = new Option(OptionType::SINGLE, '--name1');
        $opt2 = new Option(OptionType::SINGLE, '--name2');

        $this->addOptions([$opt1, $opt2]);
        $this->findOption('--name2')->shouldBe($opt2);
    }

    function it_finds_an_option_by_alias() {
        $opt1 = new Option(OptionType::SINGLE, null, '-n');
        $opt2 = new Option(OptionType::SINGLE, '--name');

        $this->addOptions([$opt1, $opt2]);
        $this->findOption('-n')->shouldBe($opt1);
    }

    function it_throws_an_error_if_option_cant_be_found() {
        $this->shouldThrow(OptionNotFoundException::class)
            ->during('findOption', ['--name']);
    }

    function it_takes_and_returns_a_handler() {
        $this->getHandler()->shouldBe(null);
        $this->setHandler('handler');
        $this->getHandler()->shouldBe('handler');
    }

    function it_is_chainable_trough_set_handler() {
        $this->setHandler('handler')->shouldBe($this);
    }

    function it_takes_and_returns_parallel() {
        $this->isParallel()->shouldBe(true);
        $this->setParallel(false);
        $this->isParallel()->shouldBe(false);
    }

    function it_is_chainable_trough_set_parallel() {
        $this->setParallel(false)->shouldBe($this);
    }

    function it_takes_and_returns_hidden() {
        $this->isHidden()->shouldBe(false);
        $this->setHidden(true);
        $this->isHidden()->shouldBe(true);
    }

    function it_is_chainable_trough_set_hidden() {
        $this->setHidden(true)->shouldBe($this);
    }

    function it_takes_and_returns_global() {
        $this->isGlobal()->shouldBe(false);
        $this->setGlobal(true);
        $this->isGlobal()->shouldBe(true);
    }

    function it_is_chainable_trough_set_global() {
        $this->setGlobal(true)->shouldBe($this);
    }

    function it_deep_clones_itself() {
        $this->argument(ArgumentType::SINGLE, 'arg');
        $this->option(OptionType::SINGLE, '--opt');
        $clone = clone $this->getWrappedObject();

        $this->getArguments()[0]->shouldBeLike($clone->getArguments()[0]);
        $this->getArguments()[0]->shouldNotBe($clone->getArguments()[0]);
        $this->getOptions()[0]->shouldBeLike($clone->getOptions()[0]);
        $this->getOptions()[0]->shouldNotBe($clone->getOptions()[0]);
    }

    function it_parses_args() {
        $arg1 = $this->argument(ArgumentType::SINGLE, 'arg1');
        $option1 = $this->option(OptionType::SINGLE_OPTIONAL, '--name');
        $option2 = $this->option(OptionType::INCREMENTAL, '--count', '-c');
        $this->parseArgs(['arg1', '--name', 'name', '-c', '-c', '-c'], false);
        
        $arg1->getValue()->shouldBe('arg1');
        $option1->getValue()->shouldBe('name');
        $option2->getValue()->shouldBe(3);
    }

    function it_parses_argv() {
        $this->parseArgv(null, false)->shouldBeArray();
    }

    function it_parses_string() {
        $this->parseString('', false)->shouldBe(['arguments' => [], 'options' => []]);
    }
}
