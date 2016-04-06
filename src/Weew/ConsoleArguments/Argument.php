<?php

namespace Weew\ConsoleArguments;

class Argument implements IArgument {
    /**
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var mixed
     */
    protected $defaultValue;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * Argument constructor.
     *
     * @param int $type
     * @param string $name
     *
     * @see ArgumentType
     */
    public function __construct(
        $type,
        $name = null
    ) {
        $this->setType($type);
        $this->setName($name);
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
     * @return IArgument
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     * @see ArgumentType
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return IArgument
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
     * @return IArgument
     */
    public function setDescription($description) {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue() {
        if ($this->defaultValue !== null) {
            return $this->defaultValue;
        } else if ($this->isMultiple()) {
            return [];
        }

        return null;
    }

    /**
     * @param $defaultValue
     *
     * @return IArgument
     */
    public function setDefaultValue($defaultValue) {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        if ($this->value === null) {
            return $this->getDefaultValue();
        }

        return $this->value;
    }

    /**
     * @param $value
     *
     * @return IArgument
     */
    public function setValue($value) {
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
     * @return bool
     */
    public function isSingle() {
        return $this->is(ArgumentType::SINGLE)
            || $this->is(ArgumentType::SINGLE_OPTIONAL);
    }

    /**
     * @return bool
     */
    public function isMultiple() {
        return $this->is(ArgumentType::MULTIPLE)
            || $this->is(ArgumentType::MULTIPLE_OPTIONAL);
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
     * @param $type
     *
     * @return bool
     */
    protected function is($type) {
        return ($this->getType() & $type) === $type;
    }
}
