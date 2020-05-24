<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Entities;

use WildPHP\Core\Entities\Model;

class SimpleTestModel extends Model
{
    protected $settable = [
        'int' => 'integer',
        'string' => 'string',
        'bool' => 'boolean',
        'array' => ['array', 'string'],
        'simpleArray' => 'array',
        'alwaysTrue',
        'date' => \DateTime::class
    ];

    protected $fillable = ['string', 'alwaysTrue'];

    protected $defaults = [
        'string' => 'Test!',
        'date' => \DateTime::class
    ];
}
