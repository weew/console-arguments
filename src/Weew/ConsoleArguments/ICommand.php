<?php

namespace Weew\ConsoleArguments;

interface ICommand {
    /**
     * @return string
     */
    function getName();

    /**
     * @param string $name
     *
     * @return ICommand
     */
    function setName($name);

    /**
     * @return string
     */
    function getDescription();

    /**
     * @param string $description
     *
     * @return ICommand
     */
    function setDescription($description);

    /**
     * @return string
     */
    function getHelp();

    /**
     * @param string $help
     *
     * @return ICommand
     */
    function setHelp($help);

    /**
     * @return bool
     */
    function isHidden();

    /**
     * @param bool $hidden
     *
     * @return ICommand
     */
    function setHidden($hidden);

    /**
     * @return bool
     */
    function isGlobal();

    /**
     * @param bool $global
     *
     * @return ICommand
     */
    function setGlobal($global);

    /**
     * @param int $type
     * @param string $name
     *
     * @return IArgument
     */
    function argument($type, $name);

    /**
     * @param IArgument $argument
     */
    function addArgument(IArgument $argument);

    /**
     * @param IArgument[] $arguments
     */
    function addArguments(array $arguments);

    /**
     * @return IArgument[]
     */
    function getArguments();

    /**
     * @param IArgument[] $arguments
     */
    function setArguments(array $arguments);

    /**
     * @param int $type
     * @param string $name
     * @param string $alias
     *
     * @return IOption
     */
    function option($type, $name = null, $alias = null);

    /**
     * @param IOption $option
     */
    function addOption(IOption $option);

    /**
     * @param IOption[] $options
     */
    function addOptions(array $options);

    /**
     * @return IOption[]
     */
    function getOptions();

    /**
     * @param IOption[] $options
     */
    function setOptions(array $options);

    /**
     * @param $name
     *
     * @return IArgument
     */
    function findArgument($name);

    /**
     * @param $nameOrAlias
     *
     * @return IOption
     */
    function findOption($nameOrAlias);

    /**
     * @return mixed
     */
    function getHandler();

    /**
     * @param $handler
     *
     * @return ICommand
     */
    function setHandler($handler);
}
