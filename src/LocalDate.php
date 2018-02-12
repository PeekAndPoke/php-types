<?php
/**
 * File was created 11.03.2015 14:51
 *
 * @author Karsten J. Gerber <kontakt@karsten-gerber.de>
 */

namespace PeekAndPoke\Types;

/**
 * LocalDate is an enhanced date that forces construction with a timezone like Europe/Berlin
 *
 * The LocalDate is immutable. All modifying methods return a new instance.
 *
 * It also integrates with Slumber to serialize to json like
 *
 * { date : "2017-10-10T11:11:11+02:00", tz : "Europe/Berlin" }
 *
 * So the receiver (e.g. the browser) can reconstruct the date with the correct timezone
 *
 * @api
 *
 * @author Karsten J. Gerber <kontakt@karsten-gerber.de>
 */
class LocalDate
{
    /** @var \DateTime */
    private $date;
    /** @var \DateTimeZone */
    private $timezone;

    /**
     * @param int                  $timestamp The unix timestamp
     * @param \DateTimeZone|string $timezone  The timezone or a string the DateTimeZone c'tor can understand
     *
     * @return LocalDate
     */
    public static function fromTimestamp($timestamp, $timezone)
    {
        return new LocalDate($timestamp, $timezone);
    }

    /**
     * @return LocalDate
     */
    public static function now()
    {
        return self::raw(new \DateTime());
    }

    /**
     * @param \DateTime $dateTime
     *
     * @return LocalDate
     */
    public static function raw(\DateTime $dateTime)
    {
        $tz = $dateTime->getTimezone();

        // normalize input coming from Javascript or Java etc...
        if ($tz->getName() === 'Z') {
            $tz = new \DateTimeZone('Etc/UTC');
        }

        return new LocalDate($dateTime, $tz);
    }

    /**
     * @param \DateTime|string|float|int $input    The date or a string the DateTime c'tor can understand or a timestamp
     * @param \DateTimeZone|string       $timezone The timezone or a string the DateTimeZone c'tor can understand
     */
    public function __construct($input, $timezone)
    {
        if (! $timezone instanceof \DateTimeZone) {
            $timezone = new \DateTimeZone((string) $timezone);
        }

        /////
        // We have to trick PHP a bit here, but why?
        //
        // Apparently things behave different, when setting a timezone like
        // a) 'Europe/Berlin'
        // b) '+02:00'
        //
        // When the date already has a timezone set then
        // a) will only set the timezone
        // b) will set the timezone and will also change the timestamp by 2 hours
        //
        // Therefore we create a fresh date, that does not have a timezone yet, set the timestamp, and then apply the
        // timezone
        //
        // See the unit tests for more as well
        ////
        if ($input instanceof \DateTime) {

            $date = (clone $input)->setTimezone($timezone);

        } elseif (is_numeric($input)) {
            // Handle numeric input. Special case is floats with micro second precision
            if (\is_float($input)) {
                $date = \DateTime::createFromFormat('U.u', sprintf('%.6f', $input));
                $date->setTimezone($timezone);
            } else {
                // otherwise we handle things as integer timestamps
                $date = (new \DateTime())->setTimestamp((int) $input)->setTimezone($timezone);
            }

        } else {
            // When we have string input, we immediately use the timezone
            // We reconstruct the date time again in order to set the timezone on the inner one
            $date = (new \DateTime($input, $timezone))->setTimezone($timezone);
        }

        $this->date     = $date;
        $this->timezone = $timezone;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->format();
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return clone $this->date;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->date->getTimestamp();
    }

    /**
     * @return \DateTimeZone
     */
    public function getTimezone()
    {
        return clone $this->timezone;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->date->getOffset();
    }

    /**
     * @return float
     */
    public function getOffsetInMinutes()
    {
        $offset = $this->getTimezone()->getOffset($this->getDate());

        return $offset / 60;
    }

    /**
     * @return float
     */
    public function getOffsetInHours()
    {
        $offset = $this->getTimezone()->getOffset($this->getDate());

        return $offset / 3600;
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function format($format = 'c')
    {
        return $this->date->format($format);
    }

    /**
     * @return LocalDate
     */
    public function getClone()
    {
        return new LocalDate($this->date, $this->timezone);
    }

    /**
     * Get the time saving shift on this date in seconds
     *
     * @return int
     */
    public function getDaylightSavingShift()
    {
        $previousNoon = $this->getStartOfPreviousDay()->modifyByHours(12);

        return $this->getOffset() - $previousNoon->getOffset();
    }

    //// Daylight saving time shift aware methods //////////////////////////////////////////////////////////////////////

    /**
     * Add hours to start of day while also respecting Daylight-saving-time shift
     *
     * E.g. on 2016-03-27T10:00:00 in Berlin is only 9 hours after the start of the day.
     *
     * @param $hours
     *
     * @return LocalDate
     */
    public function getDstStartOfDayPlusHours($hours)
    {
        $startOfDay       = $this->getStartOfDay();
        $startOfDayOffset = $startOfDay->getOffset();

        $result       = $startOfDay->modifyByHours($hours);
        $resultOffset = $result->getOffset();

        return $result->modifyBySeconds($startOfDayOffset)->modifyBySeconds(0 - $resultOffset);
    }

    //// Modification methods //////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return LocalDate
     */
    public function getStartOfDay()
    {
        return $this->modifyTime(0, 0, 0);
    }

    /**
     * Get the nearest start of a day, either the current or the next day depending on the time in the day
     *
     * @return LocalDate
     */
    public function getNearestStartOfDay()
    {
        if ($this->getHours() < 12) {
            return $this->getStartOfDay();
        }

        return $this->getStartOfNextDay();
    }

    /**
     * @return LocalDate
     */
    public function getStartOfPreviousDay()
    {
        $dateCloned = clone $this->getDate();

        // take switches between summer and winter time into account
        if ($dateCloned->format('H') > 21) {
            $dateCloned->modify('-27 hours');
        } else {
            $dateCloned->modify('-24 hours');
        }

        $temp = new LocalDate($dateCloned, $this->timezone);

        return $temp->getStartOfDay();
    }

    /**
     * @return LocalDate
     */
    public function getStartOfNextDay()
    {
        $dateCloned = clone $this->getDate();

        // take switches between summer and winter time into account
        if ($dateCloned->format('H') < 3) {
            $dateCloned->modify('+27 hours');
        } else {
            $dateCloned->modify('+24 hours');
        }

        $temp = new LocalDate($dateCloned, $this->timezone);

        return $temp->getStartOfDay();
    }

    /**
     * @return LocalDate
     */
    public function getEndOfDay()
    {
        return $this->getStartOfDay()->modifyByDays(1);
    }

    /**
     * @param int $hour
     * @param int $minute
     * @param int $second
     *
     * @return LocalDate
     */
    public function modifyTime($hour, $minute, $second)
    {
        $hour   = (int) $hour;
        $minute = (int) $minute;
        $second = (int) $second;

        $clonedDate = clone $this->date;

        $clonedDate->setTime((int) $hour, (int) $minute, (int) $second);

        return new LocalDate($clonedDate, $this->timezone);
    }

    /**
     * @param int $numSeconds
     *
     * @return LocalDate
     */
    public function modifyBySeconds($numSeconds)
    {
        return self::fromTimestamp(
            $this->getTimestamp() + (int) $numSeconds,
            $this->getTimezone()
        );
    }

    /**
     * @param float $numMinutes
     *
     * @return LocalDate
     */
    public function modifyByMinutes($numMinutes)
    {
        return $this->modifyBySeconds(((double) $numMinutes) * 60);
    }

    /**
     * @param float $numHours
     *
     * @return LocalDate
     */
    public function modifyByHours($numHours)
    {
        return $this->modifyBySeconds(((double) $numHours) * 3600);
    }

    /**
     * @param float $numDays
     *
     * @return LocalDate
     */
    public function modifyByDays($numDays)
    {
        return $this->modifyBySeconds(((double) $numDays) * 86400);
    }

    /**
     * @param int $numDays
     *
     * @return LocalDate
     */
    public function modifyByDaysDaylightSavingAware($numDays)
    {
        $numDays   = (int) $numDays;
        $timestamp = $this->getTimestamp() + (86400 * $numDays);

        $newDate = self::fromTimestamp($timestamp, $this->timezone);

        // calculate the datetime savings offsets
        $timezoneInitialDateOffset = $this->timezone->getOffset($this->date);
        $timezoneNewDateOffset     = $this->timezone->getOffset($newDate->date);

        // fix the new date if it's needed
        if ($timezoneInitialDateOffset !== $timezoneNewDateOffset) {
            $newDate = self::fromTimestamp($timestamp + ($timezoneInitialDateOffset - $timezoneNewDateOffset), $this->timezone);
        }

        return $newDate;
    }

    /**
     * @param \DateInterval|string $interval
     *
     * @deprecated use addInterval or subInterval
     *
     * @return LocalDate
     *
     * @throws \Exception When the given param is not a valid date interval
     */
    public function modifyByInterval($interval)
    {
        return $this->addInterval($interval);
    }

    /**
     * @param \DateInterval|string $interval
     *
     * @return LocalDate
     *
     * @throws \Exception When the given param is not a valid date interval
     */
    public function addInterval($interval)
    {
        if (! $interval instanceof \DateInterval) {
            $interval = new \DateInterval($interval);
        }

        $dateCloned = clone $this->date;
        $dateCloned->add($interval);

        return new LocalDate($dateCloned, $this->timezone);
    }

    /**
     * @param \DateInterval|string $interval
     *
     * @return LocalDate
     *
     * @throws \Exception When the given param is not a valid date interval
     */
    public function subInterval($interval)
    {
        if (! $interval instanceof \DateInterval) {
            $interval = new \DateInterval($interval);
        }

        $dateCloned = clone $this->date;
        $dateCloned->sub($interval);

        return new LocalDate($dateCloned, $this->timezone);
    }

    /**
     * @param $minutesInterval
     *
     * @return LocalDate
     */
    public function alignToMinutesInterval($minutesInterval)
    {
        if ($minutesInterval <= 0) {
            return $this;
        }

        // This is a work-around for the day-light-saving shift days
        // If we would use minutesIntoDay and then add those to startOfDay, we loose one hour.
        // Example would be '2015-03-29 11:20' with tz 'Europe/Berlin' would result in '2015-03-29 10:00'
        $minutesIntoDay = ((int) $this->date->format('H') * 60) + ((int) $this->date->format('i'));
        // cut off partial intervals
        $corrected = ((int) ($minutesIntoDay / $minutesInterval)) * $minutesInterval;

        return $this->getStartOfDay()->modifyByMinutes($corrected);
    }

    /**
     * @return int
     */
    public function getMinutesIntoDay()
    {
        $day  = $this->getStartOfDay();
        $diff = $this->getTimestamp() - $day->getTimestamp();

        return (int) ($diff / 60);
    }

    /**
     * Get the hours portion of the current time
     *
     * @return int
     */
    public function getHours()
    {
        return (int) $this->format('H');
    }

    /**
     * Get the weekday with Sun = 0, Mon = 1, ... Sat = 6
     *
     * @return int
     */
    public function getWeekday()
    {
        $day = (int) $this->format('N');

        if ($day === 7) {
            return 0;
        }

        return $day;
    }

    ////  COMPARISON METHODS  //////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Returns the data that is earlier, either the current or the other
     *
     * @param LocalDate|\DateTime $other
     *
     * @return LocalDate
     */
    public function min($other)
    {
        $other = $this->ensure($other);

        return $this->isBefore($other) ? $this : $other;
    }

    /**
     * Returns the data that is later, either the current or the other
     *
     * @param LocalDate|\DateTime $other
     *
     * @return LocalDate
     */
    public function max($other)
    {
        $other = $this->ensure($other);

        return $this->isAfter($other) ? $this : $other;
    }

    /**
     * Get the number of seconds between this and the other.
     *
     * The result will be negative when this is after the other
     *
     * @param LocalDate|\DateTime $other
     *
     * @return float
     */
    public function diffInSeconds($other)
    {
        $other = $this->ensure($other);

        return ($other->getTimestamp() - $this->getTimestamp());
    }

    /**
     * Get the number of minutes between this and the other.
     *
     * The result will be negative when this is after the other
     *
     * @param LocalDate|\DateTime $other
     *
     * @return float
     */
    public function diffInMinutes($other)
    {
        $other = $this->ensure($other);

        return $this->diffInSeconds($other) / 60;
    }

    /**
     * Get the number of minutes between this and the other.
     *
     * The result will be negative when this is after the other
     *
     * @param LocalDate|\DateTime $other
     *
     * @return float
     */
    public function diffInHours($other)
    {
        $other = $this->ensure($other);

        return $this->diffInSeconds($other) / 3600;
    }

    /**
     * Get the number of minutes between this and the other.
     *
     * The result will be negative when this is after the other
     *
     * @param LocalDate|\DateTime $other
     *
     * @return float
     */
    public function diffInDays($other)
    {
        $other = $this->ensure($other);

        return $this->diffInSeconds($other) / 86400;
    }

    /**
     * @param LocalDate|\DateTime $other
     *
     * @return bool
     */
    public function isBefore($other)
    {
        $other = $this->ensure($other);

        return $this->date < $other->date;
    }

    /**
     * @param LocalDate|\DateTime $other
     *
     * @return bool
     */
    public function isBeforeOrEqual($other)
    {
        $other = $this->ensure($other);

        return $this->date <= $other->date;
    }

    /**
     * @param LocalDate|\DateTime $other
     *
     * @return bool
     */
    public function isEqual($other)
    {
        $other = $this->ensure($other);

        return $this->date->getTimestamp() === $other->date->getTimestamp();
    }

    /**
     * @param LocalDate|\DateTime $other
     *
     * @return bool
     */
    public function isAfter($other)
    {
        $other = $this->ensure($other);

        return $this->date > $other->date;
    }

    /**
     * @param LocalDate|\DateTime $other
     *
     * @return bool
     */
    public function isAfterOrEqual($other)
    {
        $other = $this->ensure($other);

        return $this->date >= $other->date;
    }

    ////  PRIVATE HELPER  //////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param mixed $input
     *
     * @return LocalDate
     */
    private function ensure($input)
    {
        if ($input instanceof self) {
            return $input;
        }

        if ($input instanceof \DateTime) {
            return new LocalDate($input, $this->timezone);
        }

        return new LocalDate($input, $this->timezone);
    }
}
