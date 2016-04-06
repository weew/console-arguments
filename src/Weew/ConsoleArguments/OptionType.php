<?php

namespace Weew\ConsoleArguments;

class OptionType {
    const SINGLE = 0x11;
    const SINGLE_OPTIONAL = 0x10;
    const MULTIPLE = 0x21;
    const MULTIPLE_OPTIONAL = 0x20;
    const BOOLEAN = 0x40;
    const INCREMENTAL = 0x80;
}
