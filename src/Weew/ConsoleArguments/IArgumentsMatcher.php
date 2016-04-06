<?php

namespace Weew\ConsoleArguments;

interface IArgumentsMatcher {
    /**
     * @param ICommand[] $commands
     * @param array $args
     *
     * @return ICommand
     */
    function matchCommands(array $commands, array $args);

    /**
     * @param ICommand $commands
     * @param array $args
     */
    function matchCommand(ICommand $commands, array $args);

    /**
     * @param IArgument $argument
     * @param array $args
     *
     * @return array
     */
    function matchArgument(IArgument $argument, array $args);

    /**
     * @param IOption $option
     * @param array $args
     *
     * @return array
     */
    function matchOption(IOption $option, array $args);
}
