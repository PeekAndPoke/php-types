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
     * @param bool           $expected
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
                $now,
                $now->modifyBySeconds(1),
                true,
            ],
            [
                $now->modifyBySeconds(-1),
                $now,
                true,
            ],
            [
                null,
                null,
                false,
            ],
            [
                null,
                $now,
                false,
            ],
            [
                $now,
                null,
                false,
            ],
            [
                $now,
                $now,
                false,
            ],
            [
                $now,
                $now->modifyBySeconds(-1),
                false,
            ],
            [
                $now->modifyBySeconds(1),
                $now,
                false,
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

    /**
     * @param LocalDate|null $from
     * @param LocalDate|null $to
     * @param string         $expected
     *
     * @dataProvider provideTestToString
     */
    public function testToString($from, $to, $expected)
    {
        $subject = LocalTimeslot::createEmpty();
        $subject->setFrom($from)->setTo($to);

        self::assertSame($expected, (string) $subject, 'toString must work correctly');
    }

    public function provideTestToString()
    {
        $from = new LocalDate('2017-05-18T12:00:00', 'Etc/UTC');
        $to   = new LocalDate('2017-05-18T12:30:00', 'Etc/UTC');

        return [
            [
                null,
                null,
                '? - ?',
            ],
            [
                $from,
                null,
                $from->format() . ' - ?',
            ],
            [
                null,
                $to,
                '? - ' . $to->format(),
            ],
            [
                $from,
                $to,
                $from->format() . ' - ' . $to->format(),
            ],
        ];
    }

    /**
     * @param LocalTimeslot      $base
     * @param LocalTimeslot|null $compare
     * @param bool               $expected
     *
     * @dataProvider providerTestEquals
     */
    public function testEquals($base, $compare, $expected)
    {
        self::assertSame(
            $expected,
            $base->equals($compare),
            'equals must work correctly'
        );
    }

    public function providerTestEquals()
    {
        $now = LocalDate::now();

        $one      = LocalTimeslot::from($now->modifyBySeconds(10), $now->modifyBySeconds(20));
        $oneAgain = LocalTimeslot::from($now->modifyBySeconds(10), $now->modifyBySeconds(20));
        $two      = LocalTimeslot::from($now->modifyBySeconds(10), $now->modifyBySeconds(99999));
        $three    = LocalTimeslot::from($now->modifyBySeconds(99999), $now->modifyBySeconds(10));

        $fromOnly      = LocalTimeslot::createEmpty()->setFrom($now);
        $fromOnlyAgain = LocalTimeslot::createEmpty()->setFrom($now);

        $toOnly      = LocalTimeslot::createEmpty()->setTo($now);
        $toOnlyAgain = LocalTimeslot::createEmpty()->setTo($now);

        return [
            // positive cases
            [
                $one,
                $one,
                true,
            ],
            [
                $one,
                $oneAgain,
                true,
            ],
            [
                $fromOnly,
                $fromOnlyAgain,
                true,
            ],
            [
                $toOnly,
                $toOnlyAgain,
                true,
            ],
            // negative cases
            [
                $one,
                null,
                false,
            ],
            [
                $one,
                $two,
                false,
            ],
            [
                $one,
                $three,
                false,
            ],
            [
                $two,
                $three,
                false,
            ],
            [
                $fromOnly,
                $toOnly,
                false,
            ],
        ];
    }

    /**
     * @param LocalTimeslot $start
     * @param int           $days
     * @param LocalTimeslot $expected
     *
     * @dataProvider provideTestModifyByHours
     */
    public function testModifyByHours(LocalTimeslot $start, $days, LocalTimeslot $expected)
    {
        $result = $start->modifyByHours($days);

        self::assertNotSame($start, $result, 'A new instance must be created');

        self::assertSame(
            [$expected->getFrom()->format(), $expected->getTo()->format()],
            [$result->getFrom()->format(), $result->getTo()->format()],
            'modifyByDays must work correctly'
        );
    }

    public function provideTestModifyByHours()
    {
        $baseFrom = new LocalDate('2017-03-24T12:00:00', 'Europe/Berlin');
        $baseTo   = new LocalDate('2017-03-25T12:00:00', 'Europe/Berlin');

        return [
            [
                LocalTimeslot::from($baseFrom, $baseTo),
                24,
                LocalTimeslot::from($baseFrom->modifyByHours(24), $baseTo->modifyByHours(24)),
            ],
            [
                LocalTimeslot::from($baseFrom, $baseTo),
                48,
                LocalTimeslot::from($baseFrom->modifyByHours(48), $baseTo->modifyByHours(48)),
            ],
            [
                LocalTimeslot::from($baseFrom, $baseTo),
                72,
                LocalTimeslot::from($baseFrom->modifyByHours(72), $baseTo->modifyByHours(72)),
            ],
        ];
    }

    /**
     * @param LocalTimeslot $start
     * @param int           $days
     * @param LocalTimeslot $expected
     *
     * @dataProvider provideTestModifyByDays
     */
    public function testModifyByDays(LocalTimeslot $start, $days, LocalTimeslot $expected)
    {
        $result = $start->modifyByDays($days);

        self::assertNotSame($start, $result, 'A new instance must be created');

        self::assertSame(
            [$expected->getFrom()->format(), $expected->getTo()->format()],
            [$result->getFrom()->format(), $result->getTo()->format()],
            'modifyByDays must work correctly'
        );
    }

    public function provideTestModifyByDays()
    {
        $baseFrom = new LocalDate('2017-03-24T12:00:00', 'Europe/Berlin');
        $baseTo   = new LocalDate('2017-03-25T12:00:00', 'Europe/Berlin');

        return [
            [
                LocalTimeslot::from($baseFrom, $baseTo),
                1,
                LocalTimeslot::from($baseFrom->modifyByDays(1), $baseTo->modifyByDays(1)),
            ],
            [
                LocalTimeslot::from($baseFrom, $baseTo),
                2,
                LocalTimeslot::from($baseFrom->modifyByDays(2), $baseTo->modifyByDays(2)),
            ],
            [
                LocalTimeslot::from($baseFrom, $baseTo),
                3,
                LocalTimeslot::from($baseFrom->modifyByDays(3), $baseTo->modifyByDays(3)),
            ],
        ];
    }

    /**
     * @param LocalTimeslot $start
     * @param int           $days
     * @param LocalTimeslot $expected
     *
     * @dataProvider provideTestModifyByDaysDaylightSavingAware
     */
    public function testModifyByDaysDaylightSavingAware(LocalTimeslot $start, $days, LocalTimeslot $expected)
    {
        $result = $start->modifyByDaysDaylightSavingAware($days);

        self::assertNotSame($start, $result, 'A new instance must be created');

        self::assertSame(
            [$expected->getFrom()->format(), $expected->getTo()->format()],
            [$result->getFrom()->format(), $result->getTo()->format()],
            'modifyByDaysDaylightSavingAware must work correctly'
        );
    }

    public function provideTestModifyByDaysDaylightSavingAware()
    {
        $baseFrom = new LocalDate('2017-03-24T12:00:00', 'Europe/Berlin');
        $baseTo   = new LocalDate('2017-03-25T12:00:00', 'Europe/Berlin');

        return [
            [
                LocalTimeslot::from($baseFrom, $baseTo),
                1,
                LocalTimeslot::from($baseFrom->modifyByDaysDaylightSavingAware(1), $baseTo->modifyByDaysDaylightSavingAware(1)),
            ],
            [
                LocalTimeslot::from($baseFrom, $baseTo),
                2,
                LocalTimeslot::from($baseFrom->modifyByDaysDaylightSavingAware(2), $baseTo->modifyByDaysDaylightSavingAware(2)),
            ],
            [
                LocalTimeslot::from($baseFrom, $baseTo),
                3,
                LocalTimeslot::from($baseFrom->modifyByDaysDaylightSavingAware(3), $baseTo->modifyByDaysDaylightSavingAware(3)),
            ],
        ];
    }
}
