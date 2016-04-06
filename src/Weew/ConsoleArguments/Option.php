<?php

namespace Weew\ConsoleArguments;

use Weew\ConsoleArguments\Exceptions\InvalidOptionAliasException;
use Weew\ConsoleArguments\Exceptions\InvalidOptionNameException;
use Weew\ConsoleArguments\Exceptions\InvalidOptionValueException;

class Option implements IOption {
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var mixed
     */
    protected $defaultValue;

    /**
     * @var IArgumentsParser
     */
    protected $argumentsParser;

    /**
     * Option constructor.
     *
     * @param int $type
     * @param string $name
     * @param string $alias
     */
    public function __construct(
        $type,
        $name = null,
        $alias = null
    ) {
        $this->argumentsParser = $this->createArgumentsParser();

        $this->setType($type);
        $this->setName($name);
        $this->setAlias($alias);
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
     * @return IOption
     *
     * @throws InvalidOptionNameException
     */
    public function setName($name) {
        if ($name !== null && ! $this->argumentsParser->isOptionName($name)) {
            throw new InvalidOptionNameException(s(
                'A option name must have this format: "--option", got: "%s"', $name
            ));
        }

        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlias() {
        return $this->alias;
    }

    /**
     * @param string $alias
     *
     * @return IOption
     *
     * @throws InvalidOptionAliasException
     */
    public function setAlias($alias) {
        if ($alias !== null && ! $this->argumentsParser->isOptionAlias($alias)) {
            throw new InvalidOptionAliasException(s(
                'A option alias must have this format: "-f", got: "%s"', $alias
            ));
        }

        $this->alias = $alias;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameOrAlias() {
        if ($this->getName() !== null) {
            return $this->getName();
        }

        return $this->getAlias();
    }

    /**
     * @return int
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return IOption
     *
     * @see ArgumentType
     */
    public function setType($type) {
        $this->type = $type;

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
     * @return IOption
     */
    public function setDescription($description) {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool|int
     */
    public function getValue() {
        if ($this->value === null) {
            return $this->getDefaultValue();
        }

        return $this->value;
    }

    /**
     * @param bool|int $value
     *
     * @return IOption
     *
     * @throws InvalidOptionValueException
     */
    public function setValue($value) {
        if ($value !== null) {
            if ($this->isIncremental() && ! is_numeric($value)) {
                throw new InvalidOptionValueException(s(
                    'Trying to set a non numeric value "%s" on incremental option "%s".',
                    get_type($value),
                    $this->getNameOrAlias()
                ));
            }

            if ($this->isBoolean() && ! is_bool($value)) {
                throw new InvalidOptionValueException(s(
                    'Trying to set a non boolean value "%s" on boolean option "%s".',
                    get_type($value),
                    $this->getNameOrAlias()
                ));
            }

            if ($this->isSingle() && ! is_scalar($value)) {
                throw new InvalidOptionValueException(s(
                    'Trying to set a non scalar value "%s" on option "%s" ' .
                    'that expects one scalar value.',
                    get_type($value),
                    $this->getNameOrAlias()
                ));
            }

            if ($this->isMultiple() && ! is_array($value)) {
                throw new InvalidOptionValueException(s(
                    'Trying to set a non array value "%s" on option "%s" ' .
                    'that expects multiple values.',
                    get_type($value),
                    $this->getNameOrAlias()
                ));
            }
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasValue() {
        return $this->value !== null;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue() {
        if ($this->defaultValue !== null) {
            return $this->defaultValue;
        } else if ($this->isBoolean()) {
            return false;
        } else if ($this->isIncremental()) {
            return 0;
        } else if ($this->isMultiple()) {
            return [];
        }

        return null;
    }

    /**
     * @param mixed $defaultValue
     *
     * @return IOption
     */
    public function setDefaultValue($defaultValue) {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @param $nameOrAlias
     *
     * @return bool
     */
    public function hasNameOrAlias($nameOrAlias) {
        return $this->getName() === $nameOrAlias ||
        $this->getAlias() === $nameOrAlias;
    }

    /**
     * @return bool
     */
    public function isRequired() {
        return $this->is(0x01);
    }

    /**
     * @return bool
     */
    public function isOptional() {
        return ! $this->isRequired();
    }

    /**
     * @return bool
     */
    public function isSingle() {
        return $this->is(OptionType::SINGLE)
            || $this->is(OptionType::SINGLE_OPTIONAL);
    }

    /**
     * @return bool
     */
    public function isMultiple() {
        return $this->is(OptionType::MULTIPLE)
            || $this->is(OptionType::MULTIPLE_OPTIONAL);
    }

    /**
     * @return bool
     */
    public function isBoolean() {
        return $this->is(OptionType::BOOLEAN);
    }

    /**
     * @return bool
     */
    public function isIncremental() {
        return $this->is(OptionType::INCREMENTAL);
    }

    /**
     * @param $type
     *
     * @return bool
     */
    protected function is($type) {
        return ($this->getType() & $type) === $type;
    }

    /**
     * @return IArgumentsParser
     */
    protected function createArgumentsParser() {
        return new ArgumentsParser();
    }
}
