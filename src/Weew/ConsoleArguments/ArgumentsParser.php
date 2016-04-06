<?php

namespace Weew\ConsoleArguments;

use Exception;

class ArgumentsParser implements IArgumentsParser {
    /**
     * @var string
     */
    protected $regex = <<<REGEX
([a-zA-Z0-9.:_-]+|"([^"\\\\]+(?1)|\\\\.(?1)|)"|'([^'\\\\]+(?2)|\\\\.(?2)|)')s
REGEX;

    /**
     * @param $string
     *
     * @return bool
     */
    public function isOptionAlias($string) {
        return ! empty($string) &&
            is_string($string) &&
            preg_match('#^-[a-zA-Z0-9]{1}$#', $string) === 1;
    }

    /**
     * @param $string
     *
     * @return bool
     */
    public function isOptionName($string) {
        return ! empty($string) &&
            is_string($string) &&
            preg_match('#^--[a-zA-Z0-9_]{2,}$#', $string) === 1;
    }

    /**
     * @param $string
     *
     * @return bool
     */
    public function isOptionNameOrAlias($string) {
        return $this->isOptionAlias($string) || $this->isOptionName($string);
    }

    /**
     * @param string $string
     *
     * @return array
     */
    public function parse($string) {
        if (is_array($string)) {
            $string = implode(' ', $string);
        }

        $args = $this->parseString($string);
        $args = $this->unquoteMatches($args);
        $args = $this->normalizeShortOptions($args);

        return $args;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function group(array $args) {
        $groupedArgs = [];
        $option = 'arguments';

        foreach ($args as $arg) {
            if ($this->isOptionNameOrAlias($arg)) {
                $option = $arg;

                if ( ! array_has($groupedArgs, $option)) {
                    $groupedArgs[$option] = [];
                }
            } else {
                array_add($groupedArgs, $option, $arg);
            }
        }

        return $groupedArgs;
    }

    /**
     * @param array $args
     * @param string $name
     * @param string $alias
     *
     * @return array
     * @throws Exception
     */
    public function mergeNameAndAlias(array $args, $name, $alias) {
        if ( ! $name && ! $alias) {
            return $args;
        }

        $namedValues = array_take($args, $name);
        $aliasedValues = array_take($args, $alias);

        if ($namedValues === null && $aliasedValues === null) {
            return $args;
        }

        if ( ! is_array($namedValues)) {
            $namedValues = [];
        }

        if ( ! is_array($aliasedValues)) {
            $aliasedValues = [];
        }

        $mergedValues = array_merge($namedValues, $aliasedValues);

        if ($name) {
            array_set($args, $name, $mergedValues);
        } else {
            array_set($args, $alias, $mergedValues);
        }

        return $args;
    }

    /**
     * @param $string
     *
     * @return array
     */
    protected function parseString($string) {
        $matches = [];

        if (preg_match_all($this->regex, $string, $result) !== false) {
            $matches = $result[0];
        }

        return $matches;
    }

    /**
     * @param array $matches
     *
     * @return array
     */
    protected function unquoteMatches(array $matches) {
        $newMatches = [];

        foreach ($matches as $match) {
            $newMatches[] = $this->unquoteMatch($match);
        }

        return $newMatches;
    }

    /**
     * @param $match
     *
     * @return string
     */
    protected function unquoteMatch($match) {
        if (str_starts_with($match, ['"', "'"], $match)
            && str_ends_with($match, ['"', "'"], $match)) {
            $match = substr($match, 1, -1);
        }

        $match = str_replace(['\\\'', '\\\\'], ["'", '\\'], $match);
        $match = str_replace(['\\"', '\\\\'], ['"', '\\'], $match);

        return $match;
    }

    /**
     * @param array $matches
     *
     * @return array
     */
    protected function normalizeShortOptions(array $matches) {
        $newMatches = [];

        foreach ($matches as $match) {
            if ( ! str_starts_with($match, '--')
                && str_starts_with($match, '-')
                && strlen($match) > 2
            ) {
                // convert options like -abc to -a -b -c
                foreach (str_split(substr($match, 1)) as $match) {
                    $newMatches[] = "-$match";
                }
            } else {
                $newMatches[] = $match;
            }
        }

        return $newMatches;
    }
}
