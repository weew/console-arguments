# Console arguments parser

[![Build Status](https://img.shields.io/travis/weew/php-console-arguments.svg)](https://travis-ci.org/weew/php-console-arguments)
[![Code Quality](https://img.shields.io/scrutinizer/g/weew/php-console-arguments.svg)](https://scrutinizer-ci.com/g/weew/php-console-arguments)
[![Test Coverage](https://img.shields.io/coveralls/weew/php-console-arguments.svg)](https://coveralls.io/github/weew/php-console-arguments)
[![Version](https://img.shields.io/packagist/v/weew/php-console-arguments.svg)](https://packagist.org/packages/weew/php-console-arguments)
[![Licence](https://img.shields.io/packagist/l/weew/php-console-arguments.svg)](https://packagist.org/packages/weew/php-console-arguments)

## Table of contents

## Installation

`composer require weew/php-console-arguments`

## Introduction

This package allows you to easily build commands with certain expectations and then match them against a set of console arguments. It consists of two major parts, a `ArgumentsParser` and `ArgumentsMatcher`.

Note: this package has not been tested on windows.

## ArgumentsParser

Arguments parser allows you to parse console arguments and takes care of many annoying things like quotes, equitation signs, combined flags etc. The parsing process consists of two steps. The first step is all about normalization and separation of arguments into pieces. The second one is for grouping of flags and options. Lets take a look at this example:

```php
$parser = new ArgumentsParser();
// returns ['command:name', 'arg1', 'arg2', '--flag', 'custom "value', '-f', '1+1=2', '-v', '-v', '-v'];
$args = $parser->parse('command:name arg1 arg2 --flag="custom \"value" -f="1+1=2" -vvv');
```

Now you can groups these arguments.

```php
// returns ['command:name', 'arg1', 'arg2', '--flag' => ['custom "value'], '-f' => ['1+1=2'], '-v' => []]
$parser->group($args);
```

Depending on what kind of arguments you're working with, you might need grouped or ungrouped arguments to extract all the necessary information.

## ArgumentsMatcher

So now you have arguments. But working with plain arrays, ensuring that certain values are set etc. is quite annoying. Lets make it easy.

```php
$command = new Command('name', 'description');

// create an arguments
$command->argument(ArgumentType::SINGLE, 'argument');

// create an option
$command->option(OptionType::SINGLE_OPTIONAL, '--color', '-c')
    ->setDefaultValue('red')
    ->setDescription('your favorite color');
```

You can also create arguments and options like this.

```php
$argument = new Argument(ArgumentType::SINGLE, 'argument');
$command->addArgument($argument);

$option = new Option(OptionType::SINGLE, '--color', '-c');
$option
    ->setDefaultValue('red')
    ->setDescription('your favorite color');
$command->addOption($option);
```

There are several kinds of arguments and options.

```php
// a single argument that must be set
// throws in an error otherwise
ArgumentType::SINGLE;
// an optional argument
// no error will be thrown if missing
ArgumentType::SINGLE_OPTIONAL;

// takes a flexible amount of values
// at least one value must be set
// throws an error otherwise
ArgumentType::MULTIPLE;
// takes a flexible amount of value
// wont throw an error if missing
ArgumentType::MULTIPLE_OPTIONAL;

// a single argument is expected
// throws an error if option or value is missing
// can be used lilke this:
// -o arg results in -o=arg
OptionType:SINGLE;

// a single argument is expected
// will not throw any errors if option
// or value is missing
// -o results in -o=null
// -o arg results in -o=arg
OptionType:SINGLE_OPTIONAL;

// flexible amount of arguments is expected
// will throw an error if option
// or at least one value is missing
// can be used lilke this:
// -o arg1 arg2 arg3 results in -o=[arg1, arg2, arg3]
OptionType:MULTIPLE;

// flexible amount of arguments is expected
// will not throw any errors if option or
// value is missing
// can be used lilke this:
// -o results in -o=[]
// -o arg1 arg2 arg3 results in -o=[arg1, arg2, arg3]
OptionType:MULTIPLE_OPTIONAL;

// expects no value or one of these values:
// true, false, 0 or 1
// defaults to false
// will not throw any errors if missing
// can be used lilke this:
// -o results in -o=true
// -o=true|false|0|1 results in true or false
OptionType:BOOLEAN;

// expects a numeric value or no value at all
// defaults to 0
// wont throw any errors
// can be used lilke this:
// -ooo results in -o=3
// -o -oo results in -o=3
// -o=3 results in -o=3
OptionType:INCREMENTAL;
```

As soon as you have some commands defined, you can match them against the arguments. Matcher will throw exceptions if command expectations were not matched.

```
$args = $parser->parse('command arg1 arg2 --option');
$matcher = new ArgumentsMatcher();

$command = $matcher->matchCommand($command, $args);

$command->findArgument('arg')->getValue();
$command->findOption('--option')->getValue();
```

If you have multiple commands and don't know which should be used, you can let the matcher find it based on the command name. First value inside the arguments array is assumed to be a command name. Command name are matched intelligently. So a command with name `command` will match `command` as well as `c` and `com`. If multiple commands qualify for the same name, an exceptions will be thrown.

```php
$matcher->matchCommands($commands, $args);
```

You can also match only an option or an arguments the same way, just use `matchArgument` and `matchOption` methods.
