<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

class ParsedIrcMessage
{
    /**
     * @var array
     */
    public $tags = [];
    /**
     * @var string
     */
    public $prefix = '';
    /**
     * @var string
     */
    public $verb = '';
    /**
     * @var array
     */
    public $args = [];
}