<?php

namespace Weew\ConsoleArguments;

interface IArgumentsMatcher {
    /**
     * @param ICommand[] $commands
     * @param array $groupedArgs
     * @param bool $strict
     *
     * @return array[ICommand, array]
     */
    function matchCommands(array $commands, array $groupedArgs, $strict = true);

    /**
     * @param ICommand $commands
     * @param array $groupedArgs
     * @param bool $strict
     *
     * @return array
     */
    function matchCommand(ICommand $commands, array $groupedArgs, $strict = true);

    /**
     * @param IArgument $argument
     * @param array $groupedArgs
     * @param bool $strict
     *
     * @return array
     */
    function matchArgument(IArgument $argument, array $groupedArgs, $strict = true);

    /**
     * @param IOption $option
     * @param array $groupedArgs
     * @param bool $strict
     *
     * @return array
     */
    function matchOption(IOption $option, array $groupedArgs, $strict = true);
}
