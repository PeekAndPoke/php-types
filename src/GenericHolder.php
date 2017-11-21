<?php
/**
 * Created by gerk on 21.11.17 06:34
 */

namespace PeekAndPoke\Types;

/**
 * @author Karsten J. Gerber <kontakt@karsten-gerber.de>
 */
class GenericHolder implements ValueHolder
{
    /** @var mixed  */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
