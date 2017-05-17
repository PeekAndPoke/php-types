<?php
/**
 * Created by gerk on 17.05.17 21:34
 */

namespace PeekAndPoke\Types;

use PHPUnit\Framework\TestCase;

/**
 * @author Karsten J. Gerber <kontakt@karsten-gerber.de>
 */
class LocalTimeslotTest extends TestCase
{
    public function testStaticCreateEmpty()
    {
        $subject = LocalTimeslot::createEmpty();

        self::assertNull($subject->getFrom(), 'From must be null for empty timeslot');

        self::assertNull($subject->getTo(), 'To must be null for empty timeslot');

        self::assertFalse($subject->isValid(), 'An empty timeslot must not be valid');
    }

    /**
     * @param LocalDate|null $from
     * @param LocalDate|null $to
     *
     * @dataProvider provideTestIsValid
     */
    public function testIsValid($from, $to, $expected)
    {
        $subject = LocalTimeslot::createEmpty();
        $subject->setFrom($from)->setTo($to);

        $this->assertSame($expected, $subject->isValid(), 'isValid() must work correctly');
    }

    public function provideTestIsValid()
    {
        $now = LocalDate::now();

        return [
            [
                $now, $now->modifyBySeconds(1), true,
            ],
            [
                $now->modifyBySeconds(-1), $now, true,
            ],
            [
                null, null, false
            ],
            [
                null, $now, false,
            ],
            [
                $now, null, false,
            ],
            [
                $now, $now, false,
            ],
            [
                $now, $now->modifyBySeconds(-1), false,
            ],
            [
                $now->modifyBySeconds(1), $now, false,
            ],
        ];
    }

    public function testGetDurationForEmptyTimeslot()
    {
        $subject = LocalTimeslot::createEmpty();

        self::assertSame(0, $subject->getDurationInSecs(), 'Empty timeslot must have a duration of 0 secs');

        self::assertSame(0, $subject->getDurationInMins(), 'Empty timeslot must have a duration of 0 mins');
    }

    public function testGetDuration()
    {
        $now = LocalDate::now();

        $subject = LocalTimeslot::from($now, $now->modifyBySeconds(90));

        self::assertSame(90, $subject->getDurationInSecs(), 'getDurationInSecs must work correctly');

        self::assertSame(1.5, $subject->getDurationInMins(), 'getDurationInMins must work correctly');
    }

}
