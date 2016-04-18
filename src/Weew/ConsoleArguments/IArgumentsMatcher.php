<?php

namespace Weew\ConsoleArguments;

interface IArgumentsMatcher {
    /**
     * @param ICommand[] $commands
     * @param array $args
     * @param bool $strict
     *
     * @return ICommand
     */
    function matchCommands(array $commands, array $args, $strict = true);

    /**
     * @param ICommand $commands
     * @param array $args
     * @param bool $strict
     *
     * @return array
     */
    function matchCommand(ICommand $commands, array $args, $strict = true);

    /**
     * @param IArgument $argument
     * @param array $args
     * @param bool $strict
     *
     * @return array
     */
    function matchArgument(IArgument $argument, array $args, $strict = true);

    /**
     * @param IOption $option
     * @param array $args
     * @param bool $strict
     *
     * @return array
     */
    function matchOption(IOption $option, array $args, $strict = true);
}
