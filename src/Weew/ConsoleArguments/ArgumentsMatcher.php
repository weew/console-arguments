<?php

namespace Weew\ConsoleArguments;

use Weew\ConsoleArguments\Exceptions\AmbiguousCommandException;
use Weew\ConsoleArguments\Exceptions\CommandNotFoundException;
use Weew\ConsoleArguments\Exceptions\InvalidOptionValueException;
use Weew\ConsoleArguments\Exceptions\MissingArgumentValueException;
use Weew\ConsoleArguments\Exceptions\MissingCommandNameException;
use Weew\ConsoleArguments\Exceptions\MissingOptionValueException;
use Weew\ConsoleArguments\Exceptions\TooManyArgumentValuesException;
use Weew\ConsoleArguments\Exceptions\UnknownOptionException;

class ArgumentsMatcher implements IArgumentsMatcher {
    /**
     * @var IArgumentsParser
     */
    protected $argumentsParser;

    /**
     * ArgumentsMatcher constructor.
     *
     * @param IArgumentsParser $argumentsParser
     */
    public function __construct(IArgumentsParser $argumentsParser = null) {
        if ( ! $argumentsParser instanceof IArgumentsParser) {
            $argumentsParser = $this->createArgumentsParser();
        }

        $this->argumentsParser = $argumentsParser;
    }

    /**
     * @param array $args
     *
     * @return array
     * @throws MissingCommandNameException
     */
    public function matchCommandName(array $args) {
        $commandName = null;

        if (array_has($args, 'arguments')) {
            $commandName = (string) array_shift($args['arguments']);
        }

        if ( ! $commandName || str_starts_with($commandName, '-')) {
            throw new MissingCommandNameException(
                'You must provide a valid command name.'
            );
        }

        return [$commandName, $args];
    }

    /**
     * @param ICommand[] $commands
     * @param array $groupedArgs
     * @param bool $strict
     *
     * @return ICommand
     * @throws AmbiguousCommandException
     * @throws CommandNotFoundException
     * @throws MissingCommandNameException
     * @throws TooManyArgumentValuesException
     */
    public function matchCommands(array $commands, array $groupedArgs, $strict = true) {
        list($commandName, $groupedArgs) = $this->matchCommandName($groupedArgs);
        $command = $this->findCommand($commands, $commandName);

        $this->matchCommand($command, $groupedArgs, $strict);

        return $command;
    }

    /**
     * @param ICommand $command
     * @param array $groupedArgs
     * @param bool $strict
     *
     * @return array
     * @throws MissingArgumentValueException
     * @throws TooManyArgumentValuesException
     * @throws UnknownOptionException
     */
    public function matchCommand(ICommand $command, array $groupedArgs, $strict = true) {
        foreach ($command->getArguments() as $argument) {
            $groupedArgs = $this->matchArgument($argument, $groupedArgs, $strict);
        }

        $leftoverArgs = array_get($groupedArgs, 'arguments', []);

        if (is_array($leftoverArgs) && count($leftoverArgs) > 0 && $strict) {
            throw new TooManyArgumentValuesException(s(
                'Too many arguments, remove: %s.',
                implode(', ', $leftoverArgs)
            ));
        }

        $foundOptions = ['arguments', 'optionsCount'];

        foreach ($command->getOptions() as $option) {
            $groupedArgs = $this->matchOption($option, $groupedArgs, $strict);
            $foundOptions[] = $option->getNameOrAlias();
        }

        if ($strict) {
            foreach ($groupedArgs as $key => $arg) {
                if ( ! in_array($key, $foundOptions)) {
                    throw new UnknownOptionException(s(
                        'Invalid option "%s".',
                        $key
                    ));
                }
            }
        }

        return $groupedArgs;
    }

    /**
     * @param ICommand[] $commands
     * @param $commandName
     *
     * @return ICommand
     * @throws AmbiguousCommandException
     * @throws CommandNotFoundException
     * @throws MissingCommandNameException
     */
    public function findCommand(array $commands, $commandName) {
        $matches = [];

        foreach ($commands as $command) {
            if ($this->compareCommandName($command->getName(), $commandName)) {
                $matches[] = $command;
            }
        }

        if (count($matches) === 1) {
            return $matches[0];
        } else if (count($matches) === 0) {
            throw new CommandNotFoundException(s(
                'Could not find a command that matches "%s".',
                $commandName
            ));
        } else {
            $qualifiedCommandNames = [];

            foreach ($matches as $command) {
                $qualifiedCommandNames[] = $command->getName();
            }

            $qualifiedCommandNames = implode(', ', $qualifiedCommandNames);

            throw new AmbiguousCommandException(s(
                'Please be more precise with the command name. ' .
                'There are several commands that qualify for "%s": %s',
                $commandName,
                $qualifiedCommandNames
            ));
        }
    }

    /**
     * @param string $commandName
     * @param string $search
     *
     * @return bool
     */
    public function compareCommandName($commandName, $search) {
        $commandName = explode(':', $commandName);
        $search = explode(':', $search);

        foreach ($search as $index => $searchPart) {
            $stringPart = array_get($commandName, $index);

            if ($stringPart === null) {
                return false;
            }

            if ( ! str_starts_with($stringPart, $searchPart)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param IArgument $argument
     * @param array $groupedArgs
     * @param bool $strict
     *
     * @return array
     * @throws MissingArgumentValueException
     */
    public function matchArgument(IArgument $argument, array $groupedArgs, $strict = true) {
        $arg = null;

        if (array_has($groupedArgs, 'arguments')) {
            if ($argument->isSingle()) {
                $arg = array_shift($groupedArgs['arguments']);
            } else if ($argument->isMultiple()) {
                $arg = array_get($groupedArgs, 'arguments');
                array_set($groupedArgs, 'arguments', []);
            }
        }

        if ($argument->isRequired() && $arg === null && $strict) {
            throw new MissingArgumentValueException(s(
                'Missing argument value "%s".', $argument->getName()
            ));
        }

        $argument->setValue($arg);

        return $groupedArgs;
    }

    /**
     * @param IOption $option
     * @param array $groupedArgs
     * @param bool $strict
     *
     * @return array
     * @throws InvalidOptionValueException
     * @throws MissingOptionValueException
     */
    public function matchOption(IOption $option, array $groupedArgs, $strict = true) {
        $groupedArgs = $this->argumentsParser
            ->mergeNameAndAlias($groupedArgs, $option->getName(), $option->getAlias());

        if ($option->isIncremental()) {
            $groupedArgs = $this->matchIncrementalOption($option, $groupedArgs, $strict);
        } else if ($option->isBoolean()) {
            $groupedArgs = $this->matchBooleanOption($option, $groupedArgs, $strict);
        } else {
            $groupedArgs = $this->matchRegularOption($option, $groupedArgs, $strict);
        }

        return $groupedArgs;
    }

    /**
     * @param IOption $option
     * @param array $groupedArgs
     * @param bool $strict
     *
     * @return array
     */
    protected function matchIncrementalOption(IOption $option, array $groupedArgs, $strict) {
        if (array_has($groupedArgs, $option->getNameOrAlias())) {
            $values = array_take($groupedArgs, $option->getNameOrAlias());
            $optionCount = array_get($groupedArgs, s('optionsCount.%s', $option->getNameOrAlias()), 0);
            $valueProvided = false;

            foreach ($values as $value) {
                if (is_numeric($value)) {
                    $option->setValue(intval($value));
                    $valueProvided = true;
                }
            }

            if ( ! $valueProvided) {
                $option->setValue($optionCount);
            }
        }

        return $groupedArgs;
    }

    /**
     * @param IOption $option
     * @param array $groupedArgs
     * @param bool $strict
     *
     * @return array
     * @throws InvalidOptionValueException
     */
    protected function matchBooleanOption(IOption $option, array $groupedArgs, $strict) {
        if (array_has($groupedArgs, $option->getNameOrAlias())) {
            $values = array_take($groupedArgs, $option->getNameOrAlias());

            if ($values === []) {
                $option->setValue(true);
            } else {
                $value = array_pop($values);

                if (in_array($value, [1, '1', true, 'true'], true)) {
                    $option->setValue(true);
                } else if (in_array($value, [0, '0', false, 'false'], true)) {
                    $option->setValue(false);
                } else if ($strict) {
                    throw new InvalidOptionValueException(s(
                        'Boolean option "%s" expects one of these values: 0, 1, true, false.',
                        $option->getNameOrAlias()
                    ));
                }
            }
        }

        return $groupedArgs;
    }

    /**
     * @param IOption $option
     * @param array $groupedArgs
     * @param bool $strict
     *
     * @return array
     * @throws MissingOptionValueException
     */
    protected function matchRegularOption(IOption $option, array $groupedArgs, $strict) {
        $isRequired = $option->isRequired();
        $hasValue = array_has($groupedArgs, $option->getNameOrAlias()) &&
            count(array_get($groupedArgs, $option->getNameOrAlias())) > 0;

        if ($isRequired && ! $hasValue && $strict) {
            throw new MissingOptionValueException(s(
                'Missing option value "%s".', $option->getNameOrAlias()
            ));
        }

        if (array_has($groupedArgs, $option->getNameOrAlias())) {
            $values = array_take($groupedArgs, $option->getNameOrAlias());

            if ($option->isSingle()) {
                $option->setValue(array_pop($values));
            } else if ($option->isMultiple()) {
                $option->setValue($values);
            }
        }

        return $groupedArgs;
    }

    /**
     * @return IArgumentsParser
     */
    protected function createArgumentsParser() {
        return new ArgumentsParser();
    }
}
