<?php
/**
 * File was created 12.04.2016 06:54
 */
namespace PeekAndPoke\Types;

/**
 * @api
 *
 * @author Karsten J. Gerber <kontakt@karsten-gerber.de>
 */
class LocalTimeslot
{
    /**
     * @var LocalDate
     *
     * @PeekAndPoke\Component\Slumber\Annotation\Slumber\AsLocalDate()
     */
    private $from;

    /**
     * @var LocalDate
     *
     * @PeekAndPoke\Component\Slumber\Annotation\Slumber\AsLocalDate()
     */
    private $to;

    /**
     * @return LocalTimeslot
     */
    public static function createEmpty()
    {
        return new static();
    }

    /**
     * @param LocalDate $from
     * @param LocalDate $to
     *
     * @return LocalTimeslot
     */
    public static function from(LocalDate $from, LocalDate $to)
    {
        $ret       = new static();
        $ret->from = $from;
        $ret->to   = $to;

        return $ret;
    }

    /**
     * LocalTimeslot cannot be constructed directly.
     *
     * @see from()
     * @see createEmpty()
     */
    protected function __construct()
    {
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return ($this->from ? $this->from->format() : '?') . ' - ' . ($this->to ? $this->to->format() : '?');
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->from !== null
               && $this->to !== null
               && $this->from->isBefore($this->to);
    }

    /**
     * @param LocalTimeslot $other
     *
     * @return bool
     */
    public function equals(LocalTimeslot $other = null)
    {
        if ($this === $other) {
            return true;
        }

        return
            // is the other there ?
            $other !== null
            // is the from the same ?
            && (
                ($this->from === null && $other->from === null) ||
                ($this->from !== null && $this->from->isEqual($other->from))
            )
            // is the to the same ?
            && (
                ($this->to === null && $other->to === null) ||
                ($this->to !== null && $this->to->isEqual($other->to))
            );
    }

    /**
     * @return LocalDate
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return LocalDate
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param LocalDate $from
     *
     * @return $this
     */
    public function setFrom(LocalDate $from = null)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param LocalDate $to
     *
     * @return $this
     */
    public function setTo(LocalDate $to = null)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Creates a new instance and moves the timeslot by the given number of hours
     *
     * @param float $numHours
     *
     * @return LocalTimeslot
     */
    public function modifyByHours($numHours)
    {
        return self::from(
            $this->from->modifyByHours($numHours),
            $this->to->modifyByHours($numHours)
        );
    }

    /**
     * Creates a new instance and moves the timeslot by the given number of days
     *
     * @param int $numDays
     *
     * @return LocalTimeslot
     */
    public function modifyByDays($numDays)
    {
        return self::from(
            $this->from->modifyByDays($numDays),
            $this->to->modifyByDays($numDays)
        );
    }

    /**
     * Creates a new instance and moves the timeslot by the given number of days
     * Takes into consideration the daylight savings possible issues
     *
     * @param int $numDays
     *
     * @return LocalTimeslot
     */
    public function modifyByDaysDaylightSavingAware($numDays)
    {
        return self::from(
            $this->from->modifyByDaysDaylightSavingAware($numDays),
            $this->to->modifyByDaysDaylightSavingAware($numDays)
        );
    }

    /**
     * @return int
     */
    public function getDurationInSecs()
    {
        if ($this->from && $this->to) {
            return $this->to->getTimestamp() - $this->from->getTimestamp();
        }

        return 0;
    }

    /**
     * @return float
     */
    public function getDurationInMins()
    {
        return $this->getDurationInSecs() / 60;
    }
}
