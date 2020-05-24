<?php

/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Entities;

use InvalidArgumentException;
use WildPHP\Core\Helpers\Validation;

class Model
{
    /**
     * The properties in this object.
     *
     * @var array
     */
    protected $properties = [];

    /**
     * Properties which can be assigned.
     *
     * @see gettype()
     * @var string[]|array[]
     */
    protected $settable = [];

    /**
     * Properties which can be mass assigned.
     * Setting this when the model is immutable makes no sense.
     *
     * @var string[]
     */
    protected $fillable = [];

    /**
     * Properties which may not be mass assigned.
     * Setting this when the model is immutable makes no sense.
     *
     * @var string[]
     */
    protected $guarded = [];

    /**
     * Make this model immutable, e.g. deny any sets.
     * Either boolean for all properties, or an array of
     * properties to make immutable.
     *
     * @var bool|array
     */
    protected $immutable = false;

    /**
     * A list of mandatory properties when first creating a model.
     * Any attempt to create a model which misses a mandatory property
     * will throw an InvalidArgumentException.
     *
     * Please note that adding properties with default values here
     * negates the purpose of making them mandatory.
     *
     * @var string[]
     */
    protected $mandatory = [];

    /**
     * A list of default values to be set when the object is initialised.
     * This list will not overwrite custom values set.
     * Any default values will still be validated.
     *
     * Please note that setting default values for mandatory
     * properties negates the purpose.
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * Model constructor.
     *
     * @param array $array
     */
    public function __construct(array $array = [])
    {
        if (!$this->hasMandatoryProperties($array)) {
            throw new InvalidArgumentException('Model is missing one or more mandatory properties');
        }

        $this->stripInvalidProperties($array);
        $this->generateDefaults();
        $this->addDefaults($array);

        $this->properties = $array;
    }

    /**
     * Strips properties from the given defaults which
     * are not mass assignable or otherwise invalid.
     *
     * @param array $array
     */
    public function stripInvalidProperties(array &$array): void
    {
        foreach ($array as $key => $value) {
            if (!$this->canFill($key) || !$this->canAssignValue($key, $value)) {
                unset($array[$key]);
            }
        }
    }

    /**
     * Checks whether the given key can be mass assigned.
     *
     * @param string $key
     * @return bool
     */
    public function canFill(string $key): bool
    {
        return empty($this->fillable) ? !in_array($key, $this->guarded) : in_array($key, $this->fillable);
    }

    /**
     * Checks whether the given value can be set on the given key.
     *
     * @param string $key
     * @param $value
     * @return bool
     */
    protected function canAssignValue(string $key, $value): bool
    {
        if (!array_key_exists($key, $this->settable)) {
            return true;
        }

        $wantedType = $this->settable[$key];

        if (is_array($wantedType) && $wantedType[0] === 'array') {
            return Validation::array(
                $value,
                $wantedType[1],
                $wantedType[2] ?? null
            );
        }

        return Validation::is($value, $wantedType);
    }

    /**
     * Generates default values for properties of known types.
     */
    protected function generateDefaults(): void
    {
        foreach ($this->settable as $key => $type) {
            if (is_numeric($key) || array_key_exists($key, $this->defaults)) {
                continue;
            }

            if (is_array($type)) {
                $type = $type[0];
            }

            $default = Validation::defaultTypeValue($type);

            if ($default !== null) {
                $this->defaults[$key] = $default;
            }
        }
    }

    /**
     * Adds default values to an array.
     *
     * @param array $array
     */
    protected function addDefaults(array &$array): void
    {
        foreach ($this->defaults as $key => $value) {
            if (array_key_exists($key, $array)) {
                continue;
            }

            if (is_string($value) && class_exists($value)) {
                $value = new $value();
            }

            $array[$key] = $value;
        }
    }

    /**
     * Checks whether all mandatory properties exist
     * in the given array.
     *
     * @param array $array
     * @return bool
     */
    protected function hasMandatoryProperties(array $array): bool
    {
        foreach ($this->mandatory as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Mass assigns this model instance with the given properties.
     *
     * @param array $properties
     */
    public function fill(array $properties): void
    {
        foreach ($properties as $key => $value) {
            if (!$this->canFill($key)) {
                continue;
            }

            $this->{$key} = $value;
        }
    }

    /**
     * Tries to get a given property from this object.
     * Returns null on failure.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function &__get(string $name)
    {
        if (!array_key_exists($name, $this->properties)) {
            // https://stackoverflow.com/a/19749730
            $this->properties[$name] = null;
        }

        return $this->properties[$name];
    }

    /**
     * Tries to set a given property on this object.
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        if ($this->isImmutable($name)) {
            throw new InvalidArgumentException('Trying to set value on an immutable model or immutable property.');
        }

        if ($this->propertyExists($name) && $this->canAssignValue($name, $value)) {
            $this->properties[$name] = $value;
        }
    }

    /**
     * Checks whether the given property is immutable.
     *
     * @param string $property
     * @return bool
     */
    protected function isImmutable(string $property): bool
    {
        return $this->immutable === true || (is_array($this->immutable) && in_array($property, $this->immutable, true));
    }

    /**
     * Checks whether the given property should exist on this model.
     *
     * @param string $key
     * @return bool
     */
    public function propertyExists(string $key): bool
    {
        return in_array($key, $this->settable, true) || array_key_exists($key, $this->settable);
    }

    /**
     * Checks whether a property is set on this object.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * Returns the properties in this object as an
     * associative array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->properties;
    }
}
