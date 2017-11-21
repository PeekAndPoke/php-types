<?php
/**
 * Created by gerk on 21.11.17 06:35
 */

namespace PeekAndPoke\Types;

use PHPUnit\Framework\TestCase;

/**
 *
 *
 * @author Karsten J. Gerber <kontakt@karsten-gerber.de>
 */
class GenericHolderTest extends TestCase
{
    /**
     * @param mixed $input
     *
     * @dataProvider provideTestSubject
     */
    public function testSubject($input)
    {
        $subject = new GenericHolder($input);

        $this->assertSame($input, $subject->getValue());
    }

    public function provideTestSubject()
    {
        return [
            [null],
            [0],
            [1],
            ['null'],
            [new \stdClass()],
        ];
    }
}
