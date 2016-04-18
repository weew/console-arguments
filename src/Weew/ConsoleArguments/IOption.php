<?php

namespace Weew\ConsoleArguments;

interface IOption {
    /**
     * @return string
     */
    function getName();

    /**
     * @param string $name
     *
     * @return IOption
     */
    function setName($name);

    /**
     * @return string
     */
    function getAlias();

    /**
     * @param string $alias
     *
     * @return IOption
     */
    function setAlias($alias);

    /**
     * @return string
     */
    function getNameOrAlias();

    /**
     * @param $nameOrAlias
     *
     * @return bool
     */
    function hasNameOrAlias($nameOrAlias);

    /**
     * @return string
     */
    function getDescription();

    /**
     * @param string $description
     *
     * @return IOption
     */
    function setDescription($description);

    /**
     * @return mixed
     */
    function getValue();

    /**
     * @param mixed $value
     *
     * @return IOption
     */
    function setValue($value);

    /**
     * @return bool
     */
    function hasValue();

    /**
     * @return mixed
     */
    function getDefaultValue();

    /**
     * @param $defaultValue
     *
     * @return IOption
     */
    function setDefaultValue($defaultValue);

    /**
     * @return int
     */
    function getType();

    /**
     * @param int $type
     *
     * @return IOption
     */
    function setType($type);

    /**
     * @return bool
     */
    function isRequired();

    /**
     * @return bool
     */
    function isOptional();

    /**
     * @return bool
     */
    function isSingle();

    /**
     * @return bool
     */
    function isMultiple();

    /**
     * @return bool
     */
    function isBoolean();

    /**
     * @return bool
     */
    function isIncremental();

    /**
     * @param array|null $argv
     * @param bool $strict
     *
     * @return array
     */
    function parseArgv(array $argv = null, $strict = true);

    /**
     * @param array $args
     * @param bool $strict
     *
     * @return array
     */
    function parseArgs(array $args, $strict = true);

    /**
     * @param string $string
     * @param bool $strict
     *
     * @return array
     */
    function parseString($string, $strict = true);
}
