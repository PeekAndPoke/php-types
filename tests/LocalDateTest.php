<?php
/**
 * File was created 08.05.2015 15:10
 *
 * @author Karsten J. Gerber <kontakt@karsten-gerber.de>
 */

namespace PeekAndPoke\Types;

use PHPUnit\Framework\TestCase;

/**
 * LocalDateTest
 *
 * TODO: what is the maximal and minimal Date in PHP?
 * TODO: should we throw exceptions, when we have an over- / underflow when modifying dates? Probably yes.
 *
 * TODO: test the method that accept \DateTime as well as LocalDate for handling both correctly
 *
 * @author Karsten J. Gerber <kontakt@karsten-gerber.de>
 */
class LocalDateTest extends TestCase
{
    public function setUp()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('LocalDate not yet supporting HHVM');
        }
    }

    /**
     * @dataProvider provideTestFromTimestamp
     *
     * @param int    $timestamp
     * @param string $timezoneStr
     * @param string $expected
     */
    public function testFromTimestamp($timestamp, $timezoneStr, $expected)
    {
        $subject = LocalDate::fromTimestamp($timestamp, $timezoneStr);

        $this->assertEquals(
            $timestamp,
            $subject->getTimestamp(),
            'Setting the timezone must NOT change the timestamp'
        );

        $this->assertEquals(
            $expected,
            $subject->format(),
            'The date must be formatted correctly'
        );
    }

    /**
     * @return array
     */
    public static function provideTestFromTimestamp()
    {
        return [
            [282828282, 'Etc/UTC', '1978-12-18T11:24:42+00:00'],
            [282828282, 'Europe/Berlin', '1978-12-18T12:24:42+01:00'],
            [282828282, 'America/Chicago', '1978-12-18T05:24:42-06:00'],
            [282828282, '+02:00', '1978-12-18T13:24:42+02:00'],
            [282828282, '+02:30', '1978-12-18T13:54:42+02:30'],
            [282828282, '-02:00', '1978-12-18T09:24:42-02:00'],
            [282828282, '-02:30', '1978-12-18T08:54:42-02:30'],
        ];
    }

    /**
     * @dataProvider provideTestRaw
     *
     * @param \DateTime $raw
     * @param string    $expectedTimeZone
     * @param string    $expectedString
     */
    public function testRaw($raw, $expectedTimeZone, $expectedString)
    {
        $subject = LocalDate::raw($raw);

        $this->assertEquals(
            $expectedString,
            $subject->format(),
            'A date created from a raw date must be formatted correctly'
        );

        $this->assertEquals(
            $expectedTimeZone,
            $subject->getTimezone()->getName(),
            'A date created from a raw date must have the correct timezone'
        );

        $this->assertEquals(
            $subject->getTimezone()->getName(),
            $expectedTimeZone,
            'The timezone must be set correctly'
        );
        $this->assertEquals(
            $subject->getDate()->getTimezone()->getName(),
            $expectedTimeZone,
            'The timezone of the inner date must be set correctly'
        );
    }

    /**
     * @return array
     */
    public static function provideTestRaw()
    {
        $default = date_default_timezone_get();

        return [
            [new \DateTime('1978-12-18'), $default, '1978-12-18T00:00:00+00:00'],
            [new \DateTime('1978-12-18T12:00'), $default, '1978-12-18T12:00:00+00:00'],
            [new \DateTime('1978-12-18T12:00:00'), $default, '1978-12-18T12:00:00+00:00'],

            [new \DateTime('1978-12-18T12:00:00+01:00'), '+01:00', '1978-12-18T12:00:00+01:00'],
            [new \DateTime('1978-12-18T12:00:00+01:30'), '+01:30', '1978-12-18T12:00:00+01:30'],
            [new \DateTime('1978-12-18T12:00:00-01:00'), '-01:00', '1978-12-18T12:00:00-01:00'],
            [new \DateTime('1978-12-18T12:00:00-01:30'), '-01:30', '1978-12-18T12:00:00-01:30'],

            [new \DateTime('1978-12-18T12:00:00.000Z'), 'Etc/UTC', '1978-12-18T12:00:00+00:00'],
            [new \DateTime('1978-12-18T12:00:00.999Z'), 'Etc/UTC', '1978-12-18T12:00:00+00:00'],
        ];
    }

    /**
     * @dataProvider provideTestFromTimestamp
     *
     * @param int    $timestamp
     * @param string $timezoneStr
     * @param string $expected
     */
    public function testConstructFromTimestamp($timestamp, $timezoneStr, $expected)
    {
        $subject = new LocalDate($timestamp, $timezoneStr);

        $this->assertEquals(
            $timestamp,
            $subject->getTimestamp(),
            'Constructing from timestamp must work'
        );

        $this->assertEquals(
            $expected,
            $subject->format(),
            'A date constructed from timestamp must be formatted correctly'
        );
    }

    public function testNow()
    {
        $now      = new \DateTime();
        $localNow = LocalDate::now();

        $this->assertLessThan(1, $localNow->getTimestamp() - $now->getTimestamp(), 'Now must be constructed correctly');
        $this->assertGreaterThanOrEqual(
            0,
            $now->getTimestamp() - $localNow->getTimestamp(),
            'Now must be constructed correctly'
        );
    }

    /**
     * @dataProvider provideTestConstructor
     *
     * @param mixed  $input
     * @param string $timezoneStr
     * @param string $expected
     */
    public function testConstruct($input, $timezoneStr, $expected)
    {
        $subject = new LocalDate($input, $timezoneStr);

        $this->assertEquals(
            $expected,
            $subject->format(),
            'A date constructed from timestamp must be formatted correctly'
        );

        $this->assertEquals($subject->getTimezone()->getName(), $timezoneStr, 'The timezone must be set correctly');
        $this->assertEquals(
            $subject->getDate()->getTimezone()->getName(),
            $timezoneStr,
            'The timezone of the inner date must be set correctly'
        );
    }

    /**
     * @return array
     */
    public static function provideTestConstructor()
    {
        $etc         = new \DateTimeZone('Etc/UTC');
        $todayPrefix = (new \DateTime('today'))->format('Y-m-d\TH:i:s');

        return [
            [282828282, 'Etc/UTC', '1978-12-18T11:24:42+00:00'],
            ['282828282', 'Etc/UTC', '1978-12-18T11:24:42+00:00'],

            ['today', 'Etc/UTC', $todayPrefix . '+00:00'],
            // TODO: what about timezone
            //           ['today',     'America/Chicago',  $todayPrefix . '-05:00'],
            ['today', '+02:00', $todayPrefix . '+02:00'],
            ['today', '-02:00', $todayPrefix . '-02:00'],

            ['1978-12-18', 'Etc/UTC', '1978-12-18T00:00:00+00:00'],
            ['1978-12-18', 'Europe/Berlin', '1978-12-18T00:00:00+01:00'],
            ['1978-12-18', 'America/Chicago', '1978-12-18T00:00:00-06:00'],
            ['1978-12-18', '+02:00', '1978-12-18T00:00:00+02:00'],
            ['1978-12-18', '-02:00', '1978-12-18T00:00:00-02:00'],

            [new \DateTime('1978-12-18', $etc), 'Etc/UTC', '1978-12-18T00:00:00+00:00'],
            [new \DateTime('1978-12-18', $etc), 'Europe/Berlin', '1978-12-18T01:00:00+01:00'],
            [new \DateTime('1978-12-18', $etc), 'America/Chicago', '1978-12-17T18:00:00-06:00'],

            ['1978-12-18T12:00', 'Etc/UTC', '1978-12-18T12:00:00+00:00'],
            ['1978-12-18T12:00', 'Europe/Berlin', '1978-12-18T12:00:00+01:00'],
            ['1978-12-18T12:00', 'America/Chicago', '1978-12-18T12:00:00-06:00'],

            [new \DateTime('1978-12-18T12:00', $etc), 'Etc/UTC', '1978-12-18T12:00:00+00:00'],
            [new \DateTime('1978-12-18T12:00', $etc), 'Europe/Berlin', '1978-12-18T13:00:00+01:00'],
            [new \DateTime('1978-12-18T12:00', $etc), 'America/Chicago', '1978-12-18T06:00:00-06:00'],

            ['1978-12-18T12:00:00', 'Etc/UTC', '1978-12-18T12:00:00+00:00'],
            ['1978-12-18T12:00:00', 'Europe/Berlin', '1978-12-18T12:00:00+01:00'],
            ['1978-12-18T12:00:00', 'America/Chicago', '1978-12-18T12:00:00-06:00'],

            [new \DateTime('1978-12-18T12:00:00', $etc), 'Etc/UTC', '1978-12-18T12:00:00+00:00'],
            [new \DateTime('1978-12-18T12:00:00', $etc), 'Europe/Berlin', '1978-12-18T13:00:00+01:00'],
            [new \DateTime('1978-12-18T12:00:00', $etc), 'America/Chicago', '1978-12-18T06:00:00-06:00'],

            ['1978-12-18T12:00:00+01:00', 'Etc/UTC', '1978-12-18T11:00:00+00:00'],
            ['1978-12-18T12:00:00+01:00', 'Europe/Berlin', '1978-12-18T12:00:00+01:00'],
            ['1978-12-18T12:00:00+01:00', 'America/Chicago', '1978-12-18T05:00:00-06:00'],

            [new \DateTime('1978-12-18T12:00:00+0100', $etc), 'Etc/UTC', '1978-12-18T11:00:00+00:00'],
            [new \DateTime('1978-12-18T12:00:00+01:00', $etc), 'Etc/UTC', '1978-12-18T11:00:00+00:00'],
            [new \DateTime('1978-12-18T12:00:00+0100', $etc), 'Europe/Berlin', '1978-12-18T12:00:00+01:00'],
            [new \DateTime('1978-12-18T12:00:00+01:00', $etc), 'Europe/Berlin', '1978-12-18T12:00:00+01:00'],
            [new \DateTime('1978-12-18T12:00:00+0100', $etc), 'America/Chicago', '1978-12-18T05:00:00-06:00'],
            [new \DateTime('1978-12-18T12:00:00+01:00', $etc), 'America/Chicago', '1978-12-18T05:00:00-06:00'],

            ['1978-12-18T12:00:00-01:00', 'Etc/UTC', '1978-12-18T13:00:00+00:00'],
            ['1978-12-18T12:00:00-01:00', 'Europe/Berlin', '1978-12-18T14:00:00+01:00'],
            ['1978-12-18T12:00:00-01:00', 'America/Chicago', '1978-12-18T07:00:00-06:00'],

            [new \DateTime('1978-12-18T12:00:00-01:00', $etc), 'Etc/UTC', '1978-12-18T13:00:00+00:00'],
            [new \DateTime('1978-12-18T12:00:00-01:00', $etc), 'Europe/Berlin', '1978-12-18T14:00:00+01:00'],
            [new \DateTime('1978-12-18T12:00:00-01:00', $etc), 'America/Chicago', '1978-12-18T07:00:00-06:00'],
        ];
    }

    /**
     */
    public function testGetDate()
    {
        $date    = new \DateTime();
        $subject = LocalDate::raw($date);

        $this->assertNotSame($date, $subject->getDate(), 'getDate() must return a cloned date time object');
        $this->assertEquals($date->getTimestamp(), $subject->getTimestamp(), 'The returned date must have the same value');
    }

    /**
     */
    public function getTimestamp()
    {
        $subject = new LocalDate('1978-12-18T11:24:42+00:00', 'Etc/UTC');

        $this->assertEquals(282828282, $subject->getTimestamp(), 'Getting the timestamp must work');
    }

    /**
     * @dataProvider provideTestModifyBySeconds
     *
     * @param string $dateStr
     * @param string $tzStr
     * @param float  $bySeconds
     * @param string $expected
     */
    public function testModifyBySeconds($dateStr, $tzStr, $bySeconds, $expected)
    {
        $date = new LocalDate($dateStr, $tzStr);

        $mod = $date->modifyBySeconds($bySeconds);

        $this->assertEquals($expected, $mod->format(), 'Modifying a date by seconds must work');
        $this->assertEquals((int)$bySeconds, $date->diffInSeconds($mod), 'Diff in secs must be correct');
        $this->assertEquals((int)$bySeconds, 0 - $mod->diffInSeconds($date), 'Diff in secs must be correct');
    }

    /**
     * @return array
     */
    public static function provideTestModifyBySeconds()
    {
        /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
        return [
            // Test summer to winter time shift in London
            ['2015-10-24T10:00:00+01:00', 'Europe/London', 24 * 60 * 60, '2015-10-25T09:00:00+00:00'],
            ['2015-10-25T10:00:00+00:00', 'Europe/London', -24 * 60 * 60, '2015-10-24T11:00:00+01:00'],
            // Test summer to winter time shift in Berlin
            ['2015-10-24T10:00:00+02:00', 'Europe/Berlin', 24 * 60 * 60, '2015-10-25T09:00:00+01:00'],
            ['2015-10-25T10:00:00+01:00', 'Europe/Berlin', -24 * 60 * 60, '2015-10-24T11:00:00+02:00'],

            // Test winter to summer time shift in London
            ['2015-03-28T10:00:00+00:00', 'Europe/London', 24 * 60 * 60, '2015-03-29T11:00:00+01:00'],
            ['2015-03-29T10:00:00+01:00', 'Europe/London', -24 * 60 * 60, '2015-03-28T09:00:00+00:00'],
            // Test winter to summer time shift in Berlin
            ['2015-03-28T10:00:00+01:00', 'Europe/Berlin', 24 * 60 * 60, '2015-03-29T11:00:00+02:00'],
            ['2015-03-29T10:00:00+02:00', 'Europe/Berlin', -24 * 60 * 60, '2015-03-28T09:00:00+01:00'],

            // test leap-year
            ['2016-01-01T00:00:00+01:00', 'Europe/Berlin', 365 * 24 * 60 * 60, '2016-12-31T00:00:00+01:00'],
            ['2017-01-01T00:00:00+01:00', 'Europe/Berlin', -365 * 24 * 60 * 60, '2016-01-02T00:00:00+01:00'],

            // Test edge cases
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 0, '2018-01-01T00:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 1, '2018-01-01T00:00:01+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -1, '2017-12-31T23:59:59+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 0.5, '2018-01-01T00:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 1.5, '2018-01-01T00:00:01+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -1.5, '2017-12-31T23:59:59+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 365 * 24 * 60 * 60, '2019-01-01T00:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -365 * 24 * 60 * 60, '2017-01-01T00:00:00+01:00'],
        ];
    }

    /**
     * @dataProvider provideTestModifyByMinutes
     *
     * @param string $dateStr
     * @param string $tzStr
     * @param float  $byMinutes
     * @param string $expected
     */
    public function testModifyByMinutes($dateStr, $tzStr, $byMinutes, $expected)
    {
        $date = new LocalDate($dateStr, $tzStr);

        $mod = $date->modifyByMinutes($byMinutes);

        $this->assertEquals($expected, $mod->format(), 'Modifying a date by minutes must work');
        $this->assertEquals((float)$byMinutes, $date->diffInMinutes($mod), 'Diff in mins must be correct');
        $this->assertEquals((float)$byMinutes, 0 - $mod->diffInMinutes($date), 'Diff in mins must be correct');
    }

    /**
     * @return array
     */
    public static function provideTestModifyByMinutes()
    {
        /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
        return [
            // Test summer to winter time shift in London
            ['2015-10-24T10:00:00+01:00', 'Europe/London', 24 * 60, '2015-10-25T09:00:00+00:00'],
            ['2015-10-25T10:00:00+00:00', 'Europe/London', -24 * 60, '2015-10-24T11:00:00+01:00'],
            // Test summer to winter time shift in Berlin
            ['2015-10-24T10:00:00+02:00', 'Europe/Berlin', 24 * 60, '2015-10-25T09:00:00+01:00'],
            ['2015-10-25T10:00:00+01:00', 'Europe/Berlin', -24 * 60, '2015-10-24T11:00:00+02:00'],

            // Test winter to summer time shift in London
            ['2015-03-28T10:00:00+00:00', 'Europe/London', 24 * 60, '2015-03-29T11:00:00+01:00'],
            ['2015-03-29T10:00:00+01:00', 'Europe/London', -24 * 60, '2015-03-28T09:00:00+00:00'],
            // Test winter to summer time shift in Berlin
            ['2015-03-28T10:00:00+01:00', 'Europe/Berlin', 24 * 60, '2015-03-29T11:00:00+02:00'],
            ['2015-03-29T10:00:00+02:00', 'Europe/Berlin', -24 * 60, '2015-03-28T09:00:00+01:00'],

            // test leap-year
            ['2016-01-01T00:00:00+01:00', 'Europe/Berlin', 365 * 24 * 60, '2016-12-31T00:00:00+01:00'],
            ['2017-01-01T00:00:00+01:00', 'Europe/Berlin', -365 * 24 * 60, '2016-01-02T00:00:00+01:00'],

            // Test edge cases
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 0, '2018-01-01T00:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 1, '2018-01-01T00:01:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -1, '2017-12-31T23:59:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 0.5, '2018-01-01T00:00:30+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 1.5, '2018-01-01T00:01:30+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -1.5, '2017-12-31T23:58:30+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 365 * 24 * 60, '2019-01-01T00:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -365 * 24 * 60, '2017-01-01T00:00:00+01:00'],
        ];
    }

    /**
     * @dataProvider provideTestModifyByHours
     *
     * @param string $dateStr
     * @param string $tzStr
     * @param float  $byHours
     * @param string $expected
     */
    public function testModifyByHours($dateStr, $tzStr, $byHours, $expected)
    {
        $date = new LocalDate($dateStr, $tzStr);

        $mod = $date->modifyByHours($byHours);

        $this->assertEquals($expected, $mod->format(), 'Modifying a date by hours must work');
        $this->assertEquals((float)$byHours, $date->diffInHours($mod), 'Diff in hours must be correct');
        $this->assertEquals((float)$byHours, 0 - $mod->diffInHours($date), 'Diff in hours must be correct');
    }

    /**
     * @return array
     */
    public static function provideTestModifyByHours()
    {
        /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
        return [
            // Test summer to winter time shift in London
            ['2015-10-24T10:00:00+01:00', 'Europe/London', 24, '2015-10-25T09:00:00+00:00'],
            ['2015-10-25T10:00:00+00:00', 'Europe/London', -24, '2015-10-24T11:00:00+01:00'],
            // Test summer to winter time shift in Berlin
            ['2015-10-24T10:00:00+02:00', 'Europe/Berlin', 24, '2015-10-25T09:00:00+01:00'],
            ['2015-10-25T10:00:00+01:00', 'Europe/Berlin', -24, '2015-10-24T11:00:00+02:00'],

            // Test winter to summer time shift in London
            ['2015-03-28T10:00:00+00:00', 'Europe/London', 24, '2015-03-29T11:00:00+01:00'],
            ['2015-03-29T10:00:00+01:00', 'Europe/London', -24, '2015-03-28T09:00:00+00:00'],
            // Test winter to summer time shift in Berlin
            ['2015-03-28T10:00:00+01:00', 'Europe/Berlin', 24, '2015-03-29T11:00:00+02:00'],
            ['2015-03-29T10:00:00+02:00', 'Europe/Berlin', -24, '2015-03-28T09:00:00+01:00'],

            // test leap-year
            ['2016-01-01T00:00:00+01:00', 'Europe/Berlin', 365 * 24, '2016-12-31T00:00:00+01:00'],
            ['2017-01-01T00:00:00+01:00', 'Europe/Berlin', -365 * 24, '2016-01-02T00:00:00+01:00'],

            // Test edge cases
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 0, '2018-01-01T00:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 1, '2018-01-01T01:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -1, '2017-12-31T23:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 0.5, '2018-01-01T00:30:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 1.5, '2018-01-01T01:30:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -1.5, '2017-12-31T22:30:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 365 * 24, '2019-01-01T00:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -365 * 24, '2017-01-01T00:00:00+01:00'],
        ];
    }

    /**
     * @dataProvider provideTestModifyByDays
     *
     * @param string $dateStr
     * @param string $tzStr
     * @param float  $byDays
     * @param string $expected
     */
    public function testModifyByDays($dateStr, $tzStr, $byDays, $expected)
    {
        $date = new LocalDate($dateStr, $tzStr);

        $mod = $date->modifyByDays($byDays);

        $this->assertEquals($expected, $mod->format(), 'Modifying a date by days must work');
        $this->assertEquals((float)$byDays, $date->diffInDays($mod), 'Diff in days must be correct');
        $this->assertEquals((float)$byDays, 0 - $mod->diffInDays($date), 'Diff in days must be correct');
    }

    /**
     * @return array
     */
    public static function provideTestModifyByDays()
    {
        return [
            // Test summer to winter time shift in London
            ['2015-10-24T10:00:00+01:00', 'Europe/London', 1, '2015-10-25T09:00:00+00:00'],
            ['2015-10-25T10:00:00+00:00', 'Europe/London', -1, '2015-10-24T11:00:00+01:00'],
            // Test summer to winter time shift in Berlin
            ['2015-10-24T10:00:00+02:00', 'Europe/Berlin', 1, '2015-10-25T09:00:00+01:00'],
            ['2015-10-25T10:00:00+01:00', 'Europe/Berlin', -1, '2015-10-24T11:00:00+02:00'],

            // Test winter to summer time shift in London
            ['2015-03-28T10:00:00+00:00', 'Europe/London', 1, '2015-03-29T11:00:00+01:00'],
            ['2015-03-29T10:00:00+01:00', 'Europe/London', -1, '2015-03-28T09:00:00+00:00'],
            // Test winter to summer time shift in Berlin
            ['2015-03-28T10:00:00+01:00', 'Europe/Berlin', 1, '2015-03-29T11:00:00+02:00'],
            ['2015-03-29T10:00:00+02:00', 'Europe/Berlin', -1, '2015-03-28T09:00:00+01:00'],

            // test leap-year
            ['2016-01-01T00:00:00+01:00', 'Europe/Berlin', 365, '2016-12-31T00:00:00+01:00'],
            ['2017-01-01T00:00:00+01:00', 'Europe/Berlin', -365, '2016-01-02T00:00:00+01:00'],

            // Test edge cases
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 0, '2018-01-01T00:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 1, '2018-01-02T00:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -1, '2017-12-31T00:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 0.5, '2018-01-01T12:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 1.5, '2018-01-02T12:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -1.5, '2017-12-30T12:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 1 * 365.0, '2019-01-01T00:00:00+01:00'],
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -1 * 365.0, '2017-01-01T00:00:00+01:00'],

            // incl. one leap-year (2020)
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 4 * 365.0, '2021-12-31T00:00:00+01:00'],
            // incl. one leap-year (2016)
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -4 * 365.0, '2014-01-02T00:00:00+01:00'],
            // incl. one leap-year (2020, 2024)
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 8 * 365.0, '2025-12-30T00:00:00+01:00'],
            // incl. one leap-year (2016, 2012)
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -8 * 365.0, '2010-01-03T00:00:00+01:00'],
            // incl. one leap-year (2020, 2024, 2028)
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 12 * 365.0, '2029-12-29T00:00:00+01:00'],
            // incl. one leap-year (2016, 2012, 2008)
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -12 * 365.0, '2006-01-04T00:00:00+01:00'],
            // incl. one leap-year (2020, 2024, 2028, 2032)
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 16 * 365.0, '2033-12-28T00:00:00+01:00'],
            // incl. one leap-year (2016, 2012, 2008, 2004)
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -16 * 365.0, '2002-01-05T00:00:00+01:00'],
            // incl. leap-years (2020, 2024, 2028, 2032, 2036)
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', 20 * 365.0, '2037-12-27T00:00:00+01:00'],
            // incl. leap-years (2016, 2012, 2008, 2004, 2000)
            ['2018-01-01T00:00:00+01:00', 'Europe/Berlin', -20 * 365.0, '1998-01-06T00:00:00+01:00'],
        ];
    }

    /**
     * @dataProvider provideTestAddInterval
     *
     * The modification by interval MUST be aware of day light saving shifts, so one day can even be 25 or 23 hours.
     *
     * @param string $dateStr
     * @param string $tzStr
     * @param string $intervalStr
     * @param string $expectedAdd
     * @param string $expectedSub
     */
    public function testAddSubInterval($dateStr, $tzStr, $intervalStr, $expectedAdd, $expectedSub)
    {
        $localDate = new LocalDate($dateStr, $tzStr);

        // test with object parameter
        $added  = $localDate->addInterval(new \DateInterval($intervalStr));
        $subbed = $localDate->subInterval(new \DateInterval($intervalStr));

        $this->assertEquals($expectedAdd, $added->format(), 'Adding an interval must work');
        $this->assertEquals($expectedSub, $subbed->format(), 'Subbing an interval must work');

        // test with string parameter
        $added  = $localDate->addInterval($intervalStr);
        $subbed = $localDate->subInterval($intervalStr);

        $this->assertEquals($expectedAdd, $added->format(), 'Adding an interval as string must work');
        $this->assertEquals($expectedSub, $subbed->format(), 'Subbing an interval as string must work');
    }

    /**
     * @return array
     */
    public static function provideTestAddInterval()
    {
        return [
            // Test summer to winter time shift in London
            [
                '2015-10-24T10:00:00+01:00',
                'Europe/London',
                'P1D',
                '2015-10-25T10:00:00+00:00',
                '2015-10-23T10:00:00+01:00'
            ],
            [
                '2015-10-24T10:00:00+01:00',
                'Europe/London',
                'PT24H',
                '2015-10-25T09:00:00+00:00',
                '2015-10-23T10:00:00+01:00'
            ],
            [
                '2015-10-25T10:00:00+00:00',
                'Europe/London',
                'P1D',
                '2015-10-26T10:00:00+00:00',
                '2015-10-24T10:00:00+01:00'
            ],
            [
                '2015-10-25T10:00:00+00:00',
                'Europe/London',
                'PT24H',
                '2015-10-26T10:00:00+00:00',
                '2015-10-24T11:00:00+01:00'
            ],

            // Test summer to winter time shift in Berlin
            [
                '2015-10-24T10:00:00+02:00',
                'Europe/Berlin',
                'P1D',
                '2015-10-25T10:00:00+01:00',
                '2015-10-23T10:00:00+02:00'
            ],
            [
                '2015-10-24T10:00:00+02:00',
                'Europe/Berlin',
                'PT24H',
                '2015-10-25T09:00:00+01:00',
                '2015-10-23T10:00:00+02:00'
            ],
            [
                '2015-10-25T10:00:00+01:00',
                'Europe/Berlin',
                'P1D',
                '2015-10-26T10:00:00+01:00',
                '2015-10-24T10:00:00+02:00'
            ],
            [
                '2015-10-25T10:00:00+01:00',
                'Europe/Berlin',
                'PT24H',
                '2015-10-26T10:00:00+01:00',
                '2015-10-24T11:00:00+02:00'
            ],

            // Test winter to summer time shift in London
            [
                '2015-03-28T10:00:00+00:00',
                'Europe/London',
                'P1D',
                '2015-03-29T10:00:00+01:00',
                '2015-03-27T10:00:00+00:00'
            ],
            // Test winter to summer time shift in Berlin
            [
                '2015-03-28T10:00:00+01:00',
                'Europe/Berlin',
                'P1D',
                '2015-03-29T10:00:00+02:00',
                '2015-03-27T10:00:00+01:00'
            ],

            // TODO: more granular tests

            //
            //            // test leap-year
            //            ['2016-01-01T00:00:00+01:00',    365, '2016-12-31T00:00:00+01:00'],
            //            ['2017-01-01T00:00:00+01:00',  - 365, '2016-01-02T00:00:00+01:00'],
            //
            //            // Test edge cases
            //            ['2018-01-01T00:00:00+01:00',             0, '2018-01-01T00:00:00+01:00'],
            //            ['2018-01-01T00:00:00+01:00',             1, '2018-01-02T00:00:00+01:00'],
            //            ['2018-01-01T00:00:00+01:00',           - 1, '2017-12-31T00:00:00+01:00'],
            //            ['2018-01-01T00:00:00+01:00',           0.5, '2018-01-01T12:00:00+01:00'],
            //            ['2018-01-01T00:00:00+01:00',           1.5, '2018-01-02T12:00:00+01:00'],
            //            ['2018-01-01T00:00:00+01:00',         - 1.5, '2017-12-30T12:00:00+01:00'],
            //            ['2018-01-01T00:00:00+01:00',       1*365.0, '2019-01-01T00:00:00+01:00'],
            //            ['2018-01-01T00:00:00+01:00',     - 1*365.0, '2017-01-01T00:00:00+01:00'],
            //
            //            // incl. one leap-year (2020)
            //            ['2018-01-01T00:00:00+01:00',       4*365.0, '2021-12-31T00:00:00+01:00'],
            //            // incl. one leap-year (2016)
            //            ['2018-01-01T00:00:00+01:00',     - 4*365.0, '2014-01-02T00:00:00+01:00'],
            //            // incl. one leap-year (2020, 2024)
            //            ['2018-01-01T00:00:00+01:00',       8*365.0, '2025-12-30T00:00:00+01:00'],
            //            // incl. one leap-year (2016, 2012)
            //            ['2018-01-01T00:00:00+01:00',     - 8*365.0, '2010-01-03T00:00:00+01:00'],
            //            // incl. one leap-year (2020, 2024, 2028)
            //            ['2018-01-01T00:00:00+01:00',      12*365.0, '2029-12-29T00:00:00+01:00'],
            //            // incl. one leap-year (2016, 2012, 2008)
            //            ['2018-01-01T00:00:00+01:00',    - 12*365.0, '2006-01-04T00:00:00+01:00'],
            //            // incl. one leap-year (2020, 2024, 2028, 2032)
            //            ['2018-01-01T00:00:00+01:00',      16*365.0, '2033-12-28T00:00:00+01:00'],
            //            // incl. one leap-year (2016, 2012, 2008, 2004)
            //            ['2018-01-01T00:00:00+01:00',    - 16*365.0, '2002-01-05T00:00:00+01:00'],
            //            // incl. leap-years (2020, 2024, 2028, 2032, 2036)
            //            ['2018-01-01T00:00:00+01:00',      20*365.0, '2037-12-27T00:00:00+01:00'],
            //            // incl. leap-years (2016, 2012, 2008, 2004, 2000)
            //            ['2018-01-01T00:00:00+01:00',    - 20*365.0, '1998-01-06T00:00:00+01:00'],
        ];
    }

    public function testToString()
    {
        $subject = new LocalDate('2016-01-01', 'Etc/UTC');

        $this->assertEquals('2016-01-01T00:00:00+00:00', (string)$subject);
    }

    public function testGetOffset()
    {
        $winter = new LocalDate('2010-12-21', 'America/New_York');
        $summer = new LocalDate('2008-06-21', 'America/New_York');

        $this->assertEquals(-18000, $winter->getOffset());
        $this->assertEquals(-14400, $summer->getOffset());
    }

    public function testGetOffsetInMinutes()
    {
        $winter = new LocalDate('2010-12-21', 'America/New_York');
        $summer = new LocalDate('2008-06-21', 'America/New_York');

        $this->assertEquals(-18000 / 60, $winter->getOffsetInMinutes());
        $this->assertEquals(-14400 / 60, $summer->getOffsetInMinutes());
    }

    public function testGetOffsetInHours()
    {
        $winter = new LocalDate('2010-12-21', 'America/New_York');
        $summer = new LocalDate('2008-06-21', 'America/New_York');

        $this->assertEquals(-18000 / 3600, $winter->getOffsetInHours());
        $this->assertEquals(-14400 / 3600, $summer->getOffsetInHours());
    }

    public function testGetClone()
    {
        $subject = new LocalDate('2016-01-01', 'Europe/Berlin');
        $clone   = $subject->getClone();

        // the values must be the same
        $this->assertEquals($subject->getTimestamp(), $clone->getTimestamp());
        $this->assertEquals($subject->getTimezone(), $clone->getTimezone());

        // but they must be represented by other objects
        $this->assertNotSame($subject, $clone);
        $this->assertNotSame($subject->getDate(), $clone->getDate());
        $this->assertNotSame($subject->getTimezone(), $clone->getTimezone());
    }

    public function testGetDaylightSavingShift()
    {
        $subject = new LocalDate('2016-01-01', 'Europe/Berlin');
        $this->assertEquals(0, $subject->getDaylightSavingShift());

        // spring forward
        $subject = new LocalDate('2016-03-27T12:00:00', 'Europe/Berlin');
        $this->assertEquals(3600, $subject->getDaylightSavingShift());

        // and fall back
        $subject = new LocalDate('2016-10-30T12:00:00', 'Europe/Berlin');
        $this->assertEquals(-3600, $subject->getDaylightSavingShift());
    }

    public function testGetDstStartOfDayPlusHoursWhenWinterTimeStarts()
    {
        // we start on high noon of the day when summer time ends - the day that has a 25th hour
        $subject = new LocalDate('2017-10-29T12:00:00', 'Europe/Berlin');

        // when we add 11 hours to midnight we get 10;00
        $this->assertEquals(new LocalDate('2017-10-29T10:00:00', 'Europe/Berlin'), $subject->getStartOfDay()->modifyByHours(11));
        // if we do it dst aware we really get 11:00
        $this->assertEquals(new LocalDate('2017-10-29T11:00:00', 'Europe/Berlin'), $subject->getDstStartOfDayPlusHours(11));

        // when we add 24+11 hours to midnight we get 10;00
        $this->assertEquals(new LocalDate('2017-10-30T10:00:00', 'Europe/Berlin'), $subject->getStartOfDay()->modifyByHours(24 + 11));
        // if we do it dst aware we really get 11:00
        $this->assertEquals(new LocalDate('2017-10-30T11:00:00', 'Europe/Berlin'), $subject->getDstStartOfDayPlusHours(24 + 11));
    }

    public function testGetDstStartOfDayPlusHoursWhenSummerTimeStarts()
    {
        // we start on high noon of the day when winter time ends - the day that has only 23 hours
        $subject = new LocalDate('2018-03-25T12:00:00', 'Europe/Berlin');

        // when we add 11 hours to midnight we get 12;00
        $this->assertEquals(new LocalDate('2018-03-25T12:00:00', 'Europe/Berlin'), $subject->getStartOfDay()->modifyByHours(11));
        // if we do it dst aware we really get 11:00
        $this->assertEquals(new LocalDate('2018-03-25T11:00:00', 'Europe/Berlin'), $subject->getDstStartOfDayPlusHours(11));

        // when we add 24+11 hours to midnight we get 12;00
        $this->assertEquals(new LocalDate('2018-03-26T12:00:00', 'Europe/Berlin'), $subject->getStartOfDay()->modifyByHours(24 + 11));
        // if we do it dst aware we really get 11:00
        $this->assertEquals(new LocalDate('2018-03-26T11:00:00', 'Europe/Berlin'), $subject->getDstStartOfDayPlusHours(24 + 11));
    }

    public function testGetNearestStartOfDay()
    {
        $subject = new LocalDate('2016-01-01T00:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01', 'Europe/Berlin'), $subject->getNearestStartOfDay());

        $subject = new LocalDate('2016-01-01T11:59:59', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01', 'Europe/Berlin'), $subject->getNearestStartOfDay());

        $subject = new LocalDate('2016-01-01T12:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-02', 'Europe/Berlin'), $subject->getNearestStartOfDay());

        $subject = new LocalDate('2016-01-01T24:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-02', 'Europe/Berlin'), $subject->getNearestStartOfDay());

        $subject = new LocalDate('2016-01-01T24:59:59', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-02', 'Europe/Berlin'), $subject->getNearestStartOfDay());
    }

    public function testGetStartOfDay()
    {
        $subject = new LocalDate('2016-01-01T00:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01', 'Europe/Berlin'), $subject->getStartOfDay());

        $subject = new LocalDate('2016-01-01T12:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01', 'Europe/Berlin'), $subject->getStartOfDay());

        $subject = new LocalDate('2016-01-01T24:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-02', 'Europe/Berlin'), $subject->getStartOfDay());
    }

    public function testGetStartOfPreviousDay()
    {
        $subject = new LocalDate('2016-01-02T00:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01', 'Europe/Berlin'), $subject->getStartOfPreviousDay());

        $subject = new LocalDate('2016-01-02T01:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01', 'Europe/Berlin'), $subject->getStartOfPreviousDay());

        $subject = new LocalDate('2016-01-02T12:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01', 'Europe/Berlin'), $subject->getStartOfPreviousDay());

        $subject = new LocalDate('2016-01-02T23:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01', 'Europe/Berlin'), $subject->getStartOfPreviousDay());

        $subject = new LocalDate('2016-01-02T24:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-02', 'Europe/Berlin'), $subject->getStartOfPreviousDay());
    }

    public function testGetStartOfNextDay()
    {
        $subject = new LocalDate('2016-01-02T00:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-03', 'Europe/Berlin'), $subject->getStartOfNextDay());

        $subject = new LocalDate('2016-01-02T01:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-03', 'Europe/Berlin'), $subject->getStartOfNextDay());

        $subject = new LocalDate('2016-01-02T12:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-03', 'Europe/Berlin'), $subject->getStartOfNextDay());

        $subject = new LocalDate('2016-01-02T23:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-03', 'Europe/Berlin'), $subject->getStartOfNextDay());

        $subject = new LocalDate('2016-01-02T24:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-04', 'Europe/Berlin'), $subject->getStartOfNextDay());
    }

    public function testEndOfDay()
    {
        $subject = new LocalDate('2016-01-01T00:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-02', 'Europe/Berlin'), $subject->getEndOfDay());

        $subject = new LocalDate('2016-01-01T12:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-02', 'Europe/Berlin'), $subject->getEndOfDay());

        $subject = new LocalDate('2016-01-01T24:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-03', 'Europe/Berlin'), $subject->getEndOfDay());
    }

    public function testAlignToMinutesInterval()
    {
        $subject = new LocalDate('2016-01-01T00:00:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01T00:00:00', 'Europe/Berlin'), $subject->alignToMinutesInterval(0));
        $this->assertEquals(new LocalDate('2016-01-01T00:00:00', 'Europe/Berlin'), $subject->alignToMinutesInterval(7));
        $this->assertEquals(
            new LocalDate('2016-01-01T00:00:00', 'Europe/Berlin'),
            $subject->alignToMinutesInterval(60)
        );
        $this->assertEquals(
            new LocalDate('2016-01-01T00:00:00', 'Europe/Berlin'),
            $subject->alignToMinutesInterval(1440)
        );

        $subject = new LocalDate('2016-01-01T00:00:06', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01T00:00:00', 'Europe/Berlin'), $subject->alignToMinutesInterval(7));

        $subject = new LocalDate('2016-01-01T00:07:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01T00:07:00', 'Europe/Berlin'), $subject->alignToMinutesInterval(7));

        $subject = new LocalDate('2016-01-01T00:08:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01T00:07:00', 'Europe/Berlin'), $subject->alignToMinutesInterval(7));

        $subject = new LocalDate('2016-01-01T00:13:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01T00:07:00', 'Europe/Berlin'), $subject->alignToMinutesInterval(7));

        $subject = new LocalDate('2016-01-01T00:14:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01T00:14:00', 'Europe/Berlin'), $subject->alignToMinutesInterval(7));

        $subject = new LocalDate('2016-01-01T00:15:00', 'Europe/Berlin');
        $this->assertEquals(new LocalDate('2016-01-01T00:14:00', 'Europe/Berlin'), $subject->alignToMinutesInterval(7));
    }

    public function testGetMinutesIntoDay()
    {
        $subject = new LocalDate('2016-01-01T00:00:00', 'Europe/Berlin');
        $this->assertSame(0, $subject->getMinutesIntoDay());

        $subject = new LocalDate('2016-01-01T00:00:01', 'Europe/Berlin');
        $this->assertSame(0, $subject->getMinutesIntoDay());

        $subject = new LocalDate('2016-01-01T12:00:00', 'Europe/Berlin');
        $this->assertSame(720, $subject->getMinutesIntoDay());

        $subject = new LocalDate('2016-01-01T23:59:59', 'Europe/Berlin');
        $this->assertSame(1439, $subject->getMinutesIntoDay());
    }

    public function testGetWeekDay()
    {
        $subject = new LocalDate('2016-02-07', 'Europe/Berlin');
        $this->assertSame(0, $subject->getWeekday());

        $subject = new LocalDate('2016-02-08', 'Europe/Berlin');
        $this->assertSame(1, $subject->getWeekday());

        $subject = new LocalDate('2016-02-09', 'Europe/Berlin');
        $this->assertSame(2, $subject->getWeekday());

        $subject = new LocalDate('2016-02-10', 'Europe/Berlin');
        $this->assertSame(3, $subject->getWeekday());

        $subject = new LocalDate('2016-02-11', 'Europe/Berlin');
        $this->assertSame(4, $subject->getWeekday());

        $subject = new LocalDate('2016-02-12', 'Europe/Berlin');
        $this->assertSame(5, $subject->getWeekday());

        $subject = new LocalDate('2016-02-13', 'Europe/Berlin');
        $this->assertSame(6, $subject->getWeekday());

        $subject = new LocalDate('2016-02-14', 'Europe/Berlin');
        $this->assertSame(0, $subject->getWeekday());

        $subject = new LocalDate('2016-02-15', 'Europe/Berlin');
        $this->assertSame(1, $subject->getWeekday());
    }

    public function testMin()
    {
        $subject = new LocalDate('2016-02-08T00:00:01', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T00:00:00', 'Europe/Berlin');
        $this->assertEquals($compare->format('c'), $subject->min($compare)->format('c'));

        $subject = new LocalDate('2016-02-08T00:00:01', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T00:00:02', 'Europe/Berlin');
        $this->assertEquals($subject->format('c'), $subject->min($compare)->format('c'));

        $subject = new LocalDate('2016-02-08T00:00:01', 'Europe/Berlin');
        $compare = new \DateTime('2016-02-08T00:00:00', new \DateTimeZone('Europe/Berlin'));
        $this->assertEquals($compare->format('c'), $subject->min($compare)->format('c'));
    }

    public function testMax()
    {
        $subject = new LocalDate('2016-02-08T00:00:01', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T00:00:00', 'Europe/Berlin');
        $this->assertEquals($subject->format('c'), $subject->max($compare)->format('c'));

        $subject = new LocalDate('2016-02-08T00:00:01', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T00:00:02', 'Europe/Berlin');
        $this->assertEquals($compare->format('c'), $subject->max($compare)->format('c'));

        $subject = new LocalDate('2016-02-08T00:00:01', 'Europe/Berlin');
        $compare = new \DateTime('2016-02-08T00:00:00', new \DateTimeZone('Europe/Berlin'));
        $this->assertEquals($subject->format('c'), $subject->max($compare)->format('c'));
    }

    public function testIsBeforeOrEqual()
    {
        $subject = new LocalDate('2016-02-08T00:00:00', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T01:30:00', 'Europe/Berlin');
        $this->assertTrue($subject->isBeforeOrEqual($compare));

        $subject = new LocalDate('2016-02-08T01:30:00', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T01:30:00', 'Europe/Berlin');
        $this->assertTrue($subject->isBeforeOrEqual($compare));

        $subject = new LocalDate('2016-02-08T01:30:00', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T01:29:59', 'Europe/Berlin');
        $this->assertFalse($subject->isBeforeOrEqual($compare));

        // also test across time zones
        $subject = new LocalDate('2016-02-08T01:30:00', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T00:30:00', 'Europe/London');
        $this->assertTrue($subject->isBeforeOrEqual($compare));

        // also test across time zones
        $subject = new LocalDate('2016-02-08T01:30:00', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T00:29:59', 'Europe/London');
        $this->assertFalse($subject->isBeforeOrEqual($compare));
    }

    public function testIsAfterOrEqual()
    {
        $subject = new LocalDate('2016-02-08T00:00:00', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T01:30:00', 'Europe/Berlin');
        $this->assertFalse($subject->isAfterOrEqual($compare));

        $subject = new LocalDate('2016-02-08T01:30:00', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T01:30:00', 'Europe/Berlin');
        $this->assertTrue($subject->isAfterOrEqual($compare));

        $subject = new LocalDate('2016-02-08T01:30:00', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T01:29:59', 'Europe/Berlin');
        $this->assertTrue($subject->isAfterOrEqual($compare));

        // also test across time zones
        $subject = new LocalDate('2016-02-08T01:30:00', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T00:30:00', 'Europe/London');
        $this->assertTrue($subject->isAfterOrEqual($compare));

        // also test across time zones
        $subject = new LocalDate('2016-02-08T01:30:00', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T00:29:59', 'Europe/London');
        $this->assertTrue($subject->isAfterOrEqual($compare));

        // also test across time zones
        $subject = new LocalDate('2016-02-08T01:30:00', 'Europe/Berlin');
        $compare = new LocalDate('2016-02-08T00:30:01', 'Europe/London');
        $this->assertFalse($subject->isAfterOrEqual($compare));
    }
}
