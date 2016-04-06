<?php

namespace Weew\ConsoleArguments;

interface IArgument {
    /**
     * @return string
     */
    function getName();

    /**
     * @param string $name
     *
     * @return IArgument
     */
    function setName($name);

    /**
     * @return string
     */
    function getDescription();

    /**
     * @param string $description
     *
     * @return IArgument
     */
    function setDescription($description);

    /**
     * @return mixed
     */
    function getValue();

    /**
     * @param mixed $value
     *
     * @return IArgument
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
     * @return IArgument
     */
    function setDefaultValue($defaultValue);

    /**
     * @return int
     */
    function getType();

    /**
     * @param int $type
     *
     * @return IArgument
     */
    function setType($type);

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
    function isRequired();

    /**
     * @return bool
     */
    function isOptional();
}
