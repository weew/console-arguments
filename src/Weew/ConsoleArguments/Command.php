<?php

namespace Weew\ConsoleArguments;

use Weew\ConsoleArguments\Exceptions\ArgumentNotFoundException;
use Weew\ConsoleArguments\Exceptions\OptionNotFoundException;

class Command implements ICommand {
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $help;

    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * @var bool
     */
    protected $parallel = true;

    /**
     * @var bool
     */
    protected $global = false;

    /**
     * @var IArgument[]
     */
    protected $arguments = [];

    /**
     * @var IOption[]
     */
    protected $options = [];

    /**
     * @var mixed
     */
    protected $handler;

    /**
     * Command constructor.
     *
     * @param string $name
     * @param null $description
     */
    public function __construct($name = null, $description = null) {
        $this->setName($name);
        $this->setDescription($description);
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return ICommand
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return ICommand
     */
    public function setDescription($description) {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getHelp() {
        return $this->help;
    }

    /**
     * @param string $help
     *
     * @return ICommand
     */
    public function setHelp($help) {
        $this->help = $help;

        return $this;
    }

    /**
     * @return bool
     */
    public function isParallel() {
        return $this->parallel;
    }

    /**
     * @param bool $parallel
     *
     * @return ICommand
     */
    public function setParallel($parallel) {
        $this->parallel = $parallel;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHidden() {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     *
     * @return ICommand
     */
    public function setHidden($hidden) {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * @return bool
     */
    public function isGlobal() {
        return $this->global;
    }

    /**
     * @param bool $global
     *
     * @return $this
     */
    public function setGlobal($global) {
        $this->global = $global;

        return $this;
    }

    /**
     * @param int $type
     * @param string $name
     *
     * @return IArgument
     */
    public function argument($type, $name) {
        $argument = new Argument($type, $name);
        $this->addArgument($argument);

        return $argument;
    }

    /**
     * @param IArgument $argument
     */
    public function addArgument(IArgument $argument) {
        $this->arguments[] = $argument;
    }

    /**
     * @param IArgument[] $arguments
     */
    public function addArguments(array $arguments) {
        foreach ($arguments as $argument) {
            $this->addArgument($argument);
        }
    }

    /**
     * @return IArgument[]
     */
    public function getArguments() {
        return $this->arguments;
    }

    /**
     * @param IArgument[] $arguments
     */
    public function setArguments(array $arguments) {
        $this->arguments = [];
        $this->addArguments($arguments);
    }

    /**
     * @param int $type
     * @param string $name
     * @param string $alias
     *
     * @return IOption
     */
    public function option($type, $name = null, $alias = null) {
        $option = new Option($type, $name, $alias);
        $this->addOption($option);

        return $option;
    }

    /**
     * @param IOption $option
     */
    public function addOption(IOption $option) {
        $this->options[] = $option;
    }

    /**
     * @param IOption[] $options
     */
    public function addOptions(array $options) {
        foreach ($options as $option) {
            $this->addOption($option);
        }
    }

    /**
     * @return IOption[]
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @param IOption[] $options
     */
    public function setOptions(array $options) {
        $this->options = [];
        $this->addOptions($options);
    }

    /**
     * @param string $name
     *
     * @return IArgument
     * @throws ArgumentNotFoundException
     */
    public function findArgument($name) {
        foreach ($this->getArguments() as $argument) {
            if ($argument->getName() === $name) {
                return $argument;
            }
        }

        throw new ArgumentNotFoundException(s(
            'Argument "%s" does not exist.',
            $name
        ));
    }

    /**
     * @param $nameOrOption
     *
     * @return IOption
     * @throws OptionNotFoundException
     */
    public function findOption($nameOrOption) {
        foreach ($this->getOptions() as $option) {
            if ($option->hasNameOrAlias($nameOrOption)) {
                return $option;
            }
        }

        throw new OptionNotFoundException(s(
            'Option "%s" does not exist.',
            $nameOrOption
        ));
    }

    /**
     * @return mixed
     */
    public function getHandler() {
        return $this->handler;
    }

    /**
     * @param $handler
     *
     * @return ICommand
     */
    public function setHandler($handler) {
        $this->handler = $handler;

        return $this;
    }

    /**
     * @param array|null $argv
     * @param bool $strict
     *
     * @return array
     */
    public function parseArgv(array $argv = null, $strict = true) {
        if ( ! is_array($argv)) {
            global $argv;
        }

        return $this->parseArgs(array_slice($argv, 1), $strict);
    }

    /**
     * @param array $args
     * @param bool $strict
     *
     * @return array
     */
    public function parseArgs(array $args, $strict = true) {
        return $this->parseString(implode(' ', $args), $strict);
    }

    /**
     * @param $string
     * @param bool $strict
     *
     * @return array
     * @throws Exceptions\TooManyArgumentValuesException
     */
    public function parseString($string, $strict = true) {
        $parser = new ArgumentsParser();
        $matcher = new ArgumentsMatcher($parser);
        $args = $parser->parse($string);
        $args = $parser->group($args);

        return $matcher->matchCommand($this, $args, $strict);
    }

    /**
     * Clone command and all of its arguments and options.
     */
    public function __clone() {
        $arguments = [];
        $options = [];

        foreach ($this->arguments as $key => $argument) {
            $arguments[$key] = clone $argument;
        }

        foreach ($this->options as $key => $option) {
            $options[$key] = clone $option;
        }

        $this->arguments = $arguments;
        $this->options = $options;
    }
}
