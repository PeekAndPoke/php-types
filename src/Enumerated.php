<?php
/**
 * Created by gerk on 08.09.16 16:39
 */

namespace PeekAndPoke\Types;

/**
 * @author Karsten J. Gerber <kontakt@karsten-gerber.de>
 */
abstract class Enumerated implements ValueHolder
{
    /** @var string */
    private $value;
    /** @var boolean */
    private $valid = true;

    protected function __construct()
    {
        // noop
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @return string
     */
    final public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * static initializer to set up all enum values
     */
    final public static function init()
    {
        $reflect = self::getReflection();
        $props   = $reflect->getStaticProperties();

        foreach ($props as $name => $val) {
            $inst        = new static();
            $inst->value = (string) $name;
            $reflect->setStaticPropertyValue($name, $inst);
        }
    }

    /**
     * Get an instance of the enum with a value of 'null'.
     *
     * This might be handy if we need an instance of the Enum to work with but
     * we do not really care about a real value being set.
     *
     * @return static
     */
    final public static function void()
    {
        return self::from(null);
    }

    /**
     * Get Enum for the given input
     *
     * @param string $value
     *
     * @return static
     */
    final public static function from($value)
    {
        $value = (string) $value;

        // first we try to find the REAL values

        /** @var array|Enumerated[] $enumerated */
        $enumerated = self::enumerateProps();

        if (isset($enumerated[$value]) && $enumerated[$value] instanceof static) {
            return $enumerated[$value];
        }

        // then we keep track of INVALID values.
        static $invalid = [];
        // take care of the called class
        $cls = get_called_class();

        if (! isset($invalid[$cls][$value])) {

            $inst        = new static();
            $inst->value = $value;
            $inst->valid = false;

            $invalid[$cls][$value] = $inst;
        }

        return $invalid[$cls][$value];
    }

    /**
     * @return string[]
     */
    final public static function enumerateValues()
    {
        return array_keys(self::enumerateProps());
    }

    /**
     * @return array|Enumerated[]
     */
    final public static function enumerateProps()
    {
        return self::getReflection()->getStaticProperties();
    }

    /**
     * @return \ReflectionClass
     */
    final public static function getReflection()
    {
        return new \ReflectionClass(static::class);
    }
}
