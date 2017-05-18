<?php
/**
 * File was created 25.04.2016 12:56
 */

namespace PeekAndPoke\Types;

/**
 * @author Karsten J. Gerber <kontakt@karsten-gerber.de>
 */
interface Equatable
{
    /**
     * @param Equatable $other
     *
     * @return bool
     */
    public function isEqualTo($other = null);
}
