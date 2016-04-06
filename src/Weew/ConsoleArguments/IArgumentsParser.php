<?php

namespace Weew\ConsoleArguments;

interface IArgumentsParser {
    /**
     * @param string $string
     *
     * @return bool
     */
    function isOptionAlias($string);

    /**
     * @param string $string
     *
     * @return bool
     */
    function isOptionName($string);

    /**
     * @param string $string
     *
     * @return bool
     */
    function isOptionNameOrAlias($string);

    /**
     * @param string $string
     *
     * @return array
     */
    function parse($string);

    /**
     * @param array $args
     *
     * @return array
     */
    function group(array $args);

    /**
     * @param array $args
     * @param string $name
     * @param string $alias
     *
     * @return array
     */
    function mergeNameAndAlias(array $args, $name, $alias);
}
