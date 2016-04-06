<?php

namespace Weew\ConsoleArguments;

use Weew\ConsoleArguments\Exceptions\AmbiguousCommandException;
use Weew\ConsoleArguments\Exceptions\CommandNotFoundException;
use Weew\ConsoleArguments\Exceptions\InvalidOptionValueException;
use Weew\ConsoleArguments\Exceptions\MissingArgumentValueException;
use Weew\ConsoleArguments\Exceptions\MissingCommandNameException;
use Weew\ConsoleArguments\Exceptions\MissingOptionValueException;
use Weew\ConsoleArguments\Exceptions\TooManyArgumentValuesException;

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
    public function __construct(IArgumentsParser $argumentsParser) {
        $this->argumentsParser = $argumentsParser;
    }

    /**
     * @param array $notGroupedArgs
     *
     * @return array
     * @throws MissingCommandNameException
     */
    public function matchCommandName(array $notGroupedArgs) {
        $commandName = (string) array_shift($notGroupedArgs);

        if ( ! $commandName || str_starts_with($commandName, '-')) {
            throw new MissingCommandNameException(s(
                'You must provide a valid command name.'
            ));
        }

        return [$commandName, $notGroupedArgs];
    }

    /**
     * @param ICommand[] $commands
     * @param array $notGroupedArgs
     *
     * @return ICommand
     * @throws AmbiguousCommandException
     * @throws CommandNotFoundException
     * @throws MissingCommandNameException
     * @throws TooManyArgumentValuesException
     */
    public function matchCommands(array $commands, array $notGroupedArgs) {
        list($commandName, $notGroupedArgs) = $this->matchCommandName($notGroupedArgs);
        $command = $this->findCommand($commands, $commandName);

        $this->matchCommand($command, $notGroupedArgs);

        return $command;
    }

    /**
     * @param ICommand $command
     * @param array $notGroupedArgs
     *
     * @return ICommand
     * @throws MissingArgumentValueException
     * @throws TooManyArgumentValuesException
     */
    public function matchCommand(ICommand $command, array $notGroupedArgs) {
        foreach ($command->getOptions() as $option) {
            if ($option->isIncremental()) {
                $notGroupedArgs = $this->matchOption($option, $notGroupedArgs);
            }
        }

        $groupedArgs = $this->argumentsParser->group($notGroupedArgs);

        foreach ($command->getArguments() as $argument) {
            $groupedArgs = $this->matchArgument($argument, $groupedArgs);
        }

        $leftoverArgs = array_get($groupedArgs, 'arguments', []);

        if (is_array($leftoverArgs) && count($leftoverArgs) > 0) {
            throw new TooManyArgumentValuesException(s(
                'Too many arguments, remove: %s.',
                implode(', ', $leftoverArgs)
            ));
        }

        foreach ($command->getOptions() as $option) {
            if ( ! $option->isIncremental()) {
                $groupedArgs = $this->matchOption($option, $groupedArgs);
            }
        }

        return $command;
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

            foreach ($commands as $command) {
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
     *
     * @return array
     * @throws MissingArgumentValueException
     */
    public function matchArgument(IArgument $argument, array $groupedArgs) {
        $arg = null;

        if (array_has($groupedArgs, 'arguments')) {
            if ($argument->isSingle()) {
                $arg = array_shift($groupedArgs['arguments']);
            } else if ($argument->isMultiple()) {
                $arg = array_get($groupedArgs, 'arguments');
                array_set($groupedArgs, 'arguments', []);
            }
        }

        if ($argument->isRequired() && $arg === null) {
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
     *
     * @return array
     * @throws InvalidOptionValueException
     * @throws MissingOptionValueException
     */
    public function matchOption(IOption $option, array $groupedArgs) {
        if ($option->isIncremental()) {
            return $this->matchIncrementalOption($option, $groupedArgs);
        } else if ($option->isBoolean()) {
            return $this->matchBooleanOption($option, $groupedArgs);
        } else {
            return $this->matchRegularOption($option, $groupedArgs);
        }
    }

    /**
     * @param IOption $option
     * @param array $notGroupedArgs
     *
     * @return array
     */
    protected function matchIncrementalOption(IOption $option, array $notGroupedArgs) {
        $matched = false;

        foreach ($notGroupedArgs as $index => $arg) {
            if ($option->getNameOrAlias() === $arg || $option->getAlias() === $arg) {
                array_remove($notGroupedArgs, $index);
                $option->setValue($option->getValue() + 1);
                $matched = true;
            } else {
                if ($matched && (is_numeric($arg) || ! $this->argumentsParser->isOptionNameOrAlias($arg))) {
                    $option->setValue(intval($arg));
                    array_remove($notGroupedArgs, $index);
                }

                $matched = false;
            }
        }

        $notGroupedArgs = array_reset($notGroupedArgs);

        return $notGroupedArgs;
    }

    /**
     * @param IOption $option
     * @param array $groupedArgs
     *
     * @return array
     * @throws InvalidOptionValueException
     */
    protected function matchBooleanOption(IOption $option, array $groupedArgs) {
        $groupedArgs = $this->argumentsParser
            ->mergeNameAndAlias($groupedArgs, $option->getNameOrAlias(), $option->getAlias());

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
                } else {
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
     *
     * @return array
     * @throws MissingOptionValueException
     */
    protected function matchRegularOption(IOption $option, array $groupedArgs) {
        $groupedArgs = $this->argumentsParser
            ->mergeNameAndAlias($groupedArgs, $option->getName(), $option->getAlias());

        $isRequired = $option->isRequired();
        $hasValue = array_has($groupedArgs, $option->getNameOrAlias()) &&
            count(array_get($groupedArgs, $option->getNameOrAlias())) > 0;

        if ($isRequired && ! $hasValue) {
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
}
